<?php

namespace Tests\Unit;

use App\Models\GiftCertificate;
use App\Models\Location;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

/**
 * Изолированные проверки констант и аксессоров без загрузки приложения Laravel.
 */
class GiftCertificateOrderLocationTest extends TestCase
{
    public function test_gift_certificate_categories_list_is_complete(): void
    {
        $expected = [
            GiftCertificate::CATEGORY_HORECA,
            GiftCertificate::CATEGORY_RETAIL,
            GiftCertificate::CATEGORY_SERVICES,
            GiftCertificate::CATEGORY_SPORT,
            GiftCertificate::CATEGORY_ENTERTAINMENT,
            GiftCertificate::CATEGORY_EDUCATION,
        ];

        $this->assertSame($expected, GiftCertificate::CATEGORIES);
    }

    public function test_gift_certificate_status_constants_are_non_empty_strings(): void
    {
        $statuses = [
            GiftCertificate::STATUS_DRAFT,
            GiftCertificate::STATUS_ACTIVE,
            GiftCertificate::STATUS_REDEEMED,
            GiftCertificate::STATUS_EXPIRED,
            GiftCertificate::STATUS_CANCELLED,
        ];

        foreach ($statuses as $status) {
            $this->assertIsString($status);
            $this->assertNotSame('', $status);
        }

        $this->assertSame(count($statuses), count(array_unique($statuses)));
    }

    public function test_order_status_constants_values(): void
    {
        $this->assertSame('draft', Order::STATUS_DRAFT);
        $this->assertSame('pending', Order::STATUS_PENDING);
        $this->assertSame('paid', Order::STATUS_PAID);
        $this->assertSame('cancelled', Order::STATUS_CANCELLED);
    }

    public function test_location_coordinates_accessor_returns_lat_and_lng(): void
    {
        $location = new Location([
            'address' => 'Minsk',
            'lat' => 53.9,
            'lng' => 27.5667,
        ]);

        $this->assertSame([
            'lat' => $location->lat,
            'lng' => $location->lng,
        ], $location->coordinates);
    }

    public function test_location_full_address_accessor_returns_address(): void
    {
        $location = new Location(['address' => 'пр. Независимости, 1']);

        $this->assertSame('пр. Независимости, 1', $location->full_address);
    }
}
