<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Parking;

class ApiController extends Controller
{

    public function login(Request $request, Parking $parking)
    {
        $username = addslashes($request->get('username'));
        $password = addslashes($request->get('password'));

        try
        {
            $apiToken = $parking->login($username, $password);
        }
        catch (\Exception $ex)
        {
            return json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }
        return json_encode(array('status' => 'success', 'api_token' => $apiToken));
    }

    public function checkFreePlaces(Request $request, Parking $parking)
    {
        try
        {
            $parkingFreeSlots = $parking->getFreeParkingSpaces();
        }
        catch (\Exception $ex)
        {
            return json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }
        return json_encode(array('status' => 'success', 'free_parking_spaces' => $parkingFreeSlots));
    }

    public function checkCar(Request $request, Parking $parking)
    {
        $car_id = addslashes($request->get('car_id'));
        try
        {
            $bill = $parking->getCarBill($car_id);
        }
        catch (\Exception $ex)
        {
            return json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }
        return json_encode(array('bill' => $bill));
    }

    public function checkInCar(Request $request, Parking $parking)
    {

        $car_id = addslashes($request->get('car_id'));
        $category = addslashes($request->get('category'));
        $discount = addslashes($request->get('discount'));

        try
        {
            $parking->checkInCar($car_id, $category, $discount);
        }
        catch (\Exception $ex)
        {            
            return json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }

        return json_encode(array('status' => 'success'));
    }

    public function checkOutCar(Request $request, Parking $parking)
    {
        $car_id = addslashes($request->get('car_id'));

        try
        {
            $bill = $parking->checkOutCar($car_id);
        }
        catch (\Exception $ex)
        {            
            return json_encode(array('status' => 'error', 'message' => $ex->getMessage()));
        }
        return json_encode(array('status' => 'success', 'bill' => $bill));
    }

}
