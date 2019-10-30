<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Phaza\LaravelPostgis\Eloquent\PostgisTrait;
use Phaza\LaravelPostgis\Geometries\Point;
use Phaza\LaravelPostgis\Geometries\Polygon;
use Phaza\LaravelPostgis\Geometries\LineString;
use App\Area;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    public function createPolygon(Request $request) {

        $coords = $request['coords'];
        $polygon = [];

        foreach ($coords as $point) {
            array_push($polygon, new Point($point['latitude'], $point['longitude']));
        }
        array_push($polygon, new Point($coords[0]['latitude'], $coords[0]['longitude']));
        $polygon = new LineString($polygon);
        // logger()->debug($polygon);

        $polygon = new Polygon([$polygon]);

        $location = DB::table('ghana_map')->whereRaw('ST_Contains(geom, ST_GeomFromText(?, 4326))', [$polygon->toWKT()])->first();


        $duplicate = Area::whereRaw('ST_Intersects(geom, ST_GeographyFromText(?))', [$polygon->toWKT()])->count();
        $dup = Area::whereRaw('ST_Intersects(geom, ST_GeographyFromText(?))', [$polygon->toWKT()])->first();


        if( $duplicate > 0 ) {
            return response()->json([
                'address' => $dup['digital_address'],
                'message' => 'exists'
            ], 400);
        }

        $address = new Area();
        $address->type = $request['address_type'];
        $address->user_id = 1;
        $address->region = $location->region;
        $address->district = $location->district_code;
        $address->geom = $polygon;
        $address->digital_address = $this->generateDigitalAddress($location->region,$location->district_code);
        $address->save();

        return response()->json([
            'message' => 'Digital Address generated successfully',
            'address' => $address
        ], 200);
    }

    public static function getRegionShortCode($region){
        switch ($region){
            case 'NORTHERN':
                return 'NR';
                break;
            case 'EASTERN':
                return 'ER';
            case 'BRONG AHAFO':
                return 'BA';
            case 'WESTERN':
                return 'WR';
            case 'CENTRAL':
                return 'CR';
            case 'ASHANTI':
                return 'AH';
            case 'UPPER EAST':
                return 'UE';
            case 'GREATER ACCRA':
                return 'GA';
            case 'VOLTA':
                return 'VR';
            case 'UPPER WEST':
                return 'UW';
        }
    }

    public function generateDigitalAddress($region, $district) {

        do{
            $code = rand(1, 100000);
            $digital = $district . '-' . $code;

            $duplicate = Area::where('digital_address', $digital)->count();

        }while($duplicate);
        return $digital;
    }

    public function getProperty(Request $request) {

        $area = Area::where('digital_address', $request['address'])->first();
        $count = Area::where('digital_address', $request['address'])->count();
        // ST_AsText(ST_Centroid(geom))
        if( $count < 1 ) {
            return response()->json([
                'message' => "Not found"
            ], 400);
        }
        return response()->json([
            'area' => $area
        ], 200);
    }
}
