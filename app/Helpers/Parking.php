<?php

namespace App\Helpers;

use App\User as User;
use App\Car as Car;
use App\ParkedCar as ParkedCar;
use App\Category as Category;
use App\Discount as Discount;
use App\Price as Price;
use App\ParkingLog as ParkingLog;
use Mongo;

class Parking
{

    const ALL_PARKING_SPACES = 200;
    const DAY_SHIFT_START_HOUR = 8;
    const NIGHT_SHIFT_START_HOUR = 18;

    public static function login(string $username, string $password): string
    {
        $userModel = new User();
        $user = $userModel->getUserByUsername($username);

        if (!password_verify($password, $user[0]->password))
        {
            throw new \Exception('Wrong username or password');
        }
        return $user[0]->api_token;
    }

    public static function getFreeParkingSpaces(): string
    {
        $takenPlaces = 0;

        $categoryCarMap = array();
        $categoryCursor = (new Category())->getAll();
        foreach ($categoryCursor as $category)
        {
            $categoryCarMap[$category->category] = $category->taken_places;
        }

        $parkedCarModel = new ParkedCar();
        $parkedCarsCollectionCursor = $parkedCarModel->getAll();

        $carModel = new Car();

        foreach ($parkedCarsCollectionCursor as $parked_car)
        {
            $car = $carModel->getCarByCarId($parked_car->car_id);
            if (isset($car[0], $categoryCarMap[$car[0]->category]))
            {
                $takenPlaces += $categoryCarMap[$car[0]->category];
            }
        }

        return self::ALL_PARKING_SPACES - $takenPlaces;
    }

    public static function getCarBill(string $car_id): string
    {
        $checkOutTime = time();
        $carModel = new Car();
        $car = $carModel->getCarByCarId($car_id);
        if (count($car) == 0)
        {
            throw new \Exception('There is no car with this id in the parking.');
        }

        $prices = array();
        $priceModel = new Price();
        $pricingCursor = $priceModel->getAll();
        foreach ($pricingCursor as $price)
        {
            $prices[$price->category] = array('day_price' => $price->day_price, 'night_price' => $price->night_price);
        }

        $discounts = array();
        $discountModel = new Discount();
        $discountCursor = $discountModel->getAll();
        foreach ($discountCursor as $discount)
        {
            $discounts[$discount->type] = (100 - $discount->discount) / 100;
        }

        $parkedCarModel = new ParkedCar();
        $parkedCar = $parkedCarModel->getCarByCarId($car_id);
        if (count($parkedCar) == 0)
        {
            throw new \Exception('There is no car with this id in the parking.');
        }
        $iterTime = $parkedCar[0]->check_in_time;

        $bill = 0;
        do
        {
            $iterHour = date('H', $iterTime);
            if ($iterHour >= self::DAY_SHIFT_START_HOUR && $iterHour < self::NIGHT_SHIFT_START_HOUR)
            {
                $bill += $prices[$car[0]->category]['day_price'];
            }
            else
            {
                $bill += $prices[$car[0]->category]['night_price'];
            }
            $iterTime += 60 * 60; // 1h
        }
        while ($iterTime < $checkOutTime);

        $bill *= $discounts[$car[0]->discount];

        return $bill;
    }

    public static function checkInCar($car_id, $category, $discount)
    {
        if (!self::validateCategory($category))
        {
            throw new \Exception('Invalid car category.');
        }

        if (!self::validateDiscount($discount))
        {
            throw new \Exception('Invalid car discount type.');
        }

        $carCategory = (new Category())->getByCategory($category);
        if (self::getFreeParkingSpaces() < $carCategory[0]['taken_places'])
        {
            throw new \Exception('No enough space on the parking.');
        }

        $session = Mongo::get()->startSession();
        $session->startTransaction();
        try
        {
            $parkedCarModel = new ParkedCar();
            $parkedCarModel->insertParkedCar($car_id);

            $carModel = new Car();
            $carModel->insertCar($car_id, $category, $discount);

            $session->commitTransaction();
        }
        catch (\Exception $ex)
        {
            $session->abortTransaction();
            throw new \Exception('Something went wrong.');
        }
    }

    public static function checkOutCar(string $car_id): float
    {
        $session = Mongo::get()->startSession();
        $session->startTransaction();
        try
        {
            $bill = self::getCarBill($car_id);

            $parkedCarModel = new ParkedCar();
            $parkedCar = $parkedCarModel->getCarByCarId($car_id);

            if ($parkedCar[0])
            {
                $parkedCarModel->deleteParkedCarByCarId($car_id);
            }
            else
            {
                throw new \Exception('There is no car with this id in the parking.');
            }

            $parkingLog = new ParkingLog();
            $parkingLog->insertLog(array(
                'car_id' => $car_id,
                'car_check_in_time' => $parkedCar[0]->check_in_time,
                'car_check_out_time' => time(),
                'bill' => $bill)
            );

            $session->commitTransaction();
        }
        catch (\Exception $ex)
        {
            $session->abortTransaction();
            throw new \Exception('Something went wrong.');
        }

        return $bill;
    }

    public static function validateCategory(string $category): bool
    {
        $categories = array();
        $categoryModel = new Category();
        $categoryCursor = $categoryModel->getAll();
        foreach ($categoryCursor as $categoryIter)
        {
            $categories[] = $categoryIter->category;
        }
        return in_array($category, $categories);
    }

    public static function validateDiscount(string $discount): bool
    {
        $discounts = array();
        $discountModel = new Discount();
        $discountCursor = $discountModel->getAll();
        foreach ($discountCursor as $discountIter)
        {
            $discounts[] = $discountIter->type;
        }
        return in_array($discount, $discounts);
    }

}
