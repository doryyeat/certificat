<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function index(Request $request): Response
    {
        $stores = auth()->user()->organization->stores()
            ->with('images')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return inertia('Stores/Index', [
            'stores' => $stores,
        ]);
    }

    public function geocode(Request $request)
    {
        $request->validate([
            'address' => 'required|string|min:3'
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => config('app.name') . ' - Store Geocoding',
                'Accept' => 'application/json',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $data = $response->json()[0];
                return response()->json([
                    'success' => true,
                    'lat' => (float) $data['lat'],
                    'lon' => (float) $data['lon'],
                    'display_name' => $data['display_name'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Адрес не найден'
            ]);
        } catch (\Exception $e) {
            Log::error('Geocoding failed', ['address' => $request->address, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка геокодинга'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'opening_hours' => 'nullable|string|max:255',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $store = auth()->user()->organization->stores()->create($validated);

            // Сохраняем изображения
            if ($request->hasFile('images')) {
                $main_photo_id = $this->saveStoreImages($store, $request);
                if ($main_photo_id) {
                    $store->main_photo_id = $main_photo_id;
                    $store->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'store' => $store]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Store $store)
    {
        $this->authorizeStore($request, $store);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'opening_hours' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'main_image_id' => 'nullable|exists:images,id',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer|exists:images,id',
        ]);

        try {
            DB::beginTransaction();

            // Обновляем основные данные
            $store->update([
                'name' => $validated['name'],
                'address' => $validated['address'],
                'phone' => $validated['phone'],
                'opening_hours' => $validated['opening_hours'] ?? null,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ]);

            // Удаляем помеченные изображения
            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imageId) {
                    $image = $store->images()->where('id', $imageId)->first();
                    if ($image) {
                        $this->deleteImageFiles($image);
                        $image->delete();
                    }
                }
            }

            // Сохраняем новые изображения
            if ($request->hasFile('images')) {
                $main_photo_id = $this->saveStoreImages($store, $request);
                if ($main_photo_id) {
                    $store->main_photo_id = $main_photo_id;
                    $store->save();
                }
            }

            // Устанавливаем главное изображение из существующих
            if ($request->has('main_image_id')) {
                $store->main_photo_id = $request->main_image_id;
                $store->save();
            }

            DB::commit();

            // Загружаем обновленные данные
            $store->load('images');

            return response()->json(['success' => true, 'store' => $store]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Store $store)
    {
        $this->authorize('delete', $store);

        // Удаляем все изображения
        foreach ($store->images as $image) {
            $this->deleteImageFiles($image);
            $image->delete();
        }

        $store->delete();

        return response()->json(['success' => true]);
    }

    private function saveStoreImages(Store $store, Request $request)
    {
        $main_photo_id = null;
        $newMainImageIndex = $request->input('new_main_image_index');

        foreach ($request->file('images') as $index => $imageFile) {
            $filename = Str::uuid() . '.' . $imageFile->getClientOriginalExtension();

            // Сохраняем маленькое изображение
            $smallPath = $imageFile->storeAs('stores/' . $store->id . '/small', $filename, 'public');
            // Сохраняем большое изображение
            $bigPath = $imageFile->storeAs('stores/' . $store->id . '/big', $filename, 'public');

            $image = $store->images()->create([
                'small_uri' => Storage::url($smallPath),
                'big_uri' => Storage::url($bigPath),
            ]);

            // Если это главное изображение
            if ($newMainImageIndex !== null && (string)$index === (string)$newMainImageIndex) {
                $main_photo_id = $image->id;
            }
        }

        return $main_photo_id;
    }

    private function deleteImageFiles($image)
    {
        // Удаляем файлы из storage
        $smallPath = str_replace('/storage/', '', $image->small_uri);
        $bigPath = str_replace('/storage/', '', $image->big_uri);

        if (Storage::disk('public')->exists($smallPath)) {
            Storage::disk('public')->delete($smallPath);
        }
        if (Storage::disk('public')->exists($bigPath)) {
            Storage::disk('public')->delete($bigPath);
        }
    }

    private function authorizeStore(Request $request, Store $store): void
    {
        if ((int) $store->organization_id !== (int) $request->user()->organization->id) {
            abort(403);
        }
    }
}
