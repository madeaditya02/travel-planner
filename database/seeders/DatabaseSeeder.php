<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use App\Models\Place;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Activity;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user1 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'test',
            'profile_picture' => 'https://ui-avatars.com/api/?name=Test+User'
        ]);
        $user2 = User::factory()->create([
            'name' => 'Made Aditya',
            'username' => 'mdadityaa',
            'email' => 'imadeaditya4@gmail.com',
            'profile_picture' => 'https://ui-avatars.com/api/?name=Made+Aditya'
        ]);
        $user3 = User::factory()->create([
            'name' => 'Wahyu Pranata',
            'username' => 'wahyupranata',
            'email' => 'wahyupranata@gmail.com',
            'profile_picture' => 'https://ui-avatars.com/api/?name=Wahyu+Pranata'
        ]);
        $user4 = User::factory()->create([
            'name' => 'Kadek Chandra',
            'username' => 'kdkchandra',
            'email' => 'kadekchandra@gmail.com',
            'profile_picture' => 'https://ui-avatars.com/api/?name=Kadek+Chandra'
        ]);
        $user5 = User::factory()->create([
            'name' => 'Bisma Yoga',
            'username' => 'ygbismaa',
            'email' => 'bismayoga@gmail.com',
            'profile_picture' => 'https://ui-avatars.com/api/?name=Bisma+Yoga'
        ]);
        $user3->following()->attach($user2);
        $user4->following()->attach($user3);
        $id1 = Str::random(6);
        $plan1 = Plan::create([
            // 'id' => $id1,
            'name' => 'Trip to Bali',
            'start_date' => now(),
            'end_date' => now()->add(3, 'days'),
            'public_id' => (string) Str::uuid()
        ]);
        $plan1->users()->attach($user2->id, ['accepted_at' => now(), 'role' => 'Owner']);
        $place1 = Place::create([
            'id' => "ChIJwRIJzsFB0i0RQdXwd3GvJvA",
            'name' => "Sanur",
            'address' => "Sanur, Bali, Indonesia",
            'province' => "Bali",
            'types' => json_encode(["natural_feature", "establishment"]),
            "latitude" => -8.7071782,
            "longitude" => 115.26262360000001,
            "rating" => 4.5,
            "url" => "https://maps.google.com/?cid=17304711520096146753&g_mp=Cidnb29nbGUubWFwcy5wbGFjZXMudjEuUGxhY2VzLlNlYXJjaFRleHQQAhgBIAA",
            "summary" => "Colorful fishing boats dot beaches of this resort town with galleries, restaurants & an old temple",
            "photo" => "places/ChIJwRIJzsFB0i0RQdXwd3GvJvA/photos/AZLasHrcFX1nOvsMEUYu67Q2EOozgvwspRV78-1sMJ56NMyjz2rKfvvHYROTRz5_lriJf5c9TiVpRPbi2-1S8iLazypm3g8km-fAK-eypViloCCqPrYuKBmFdwvCMGVy015SbHT8UKyizTVb7Nyl11Fu2SVFH4YQyXD_d2T3-4hs4ApJOk_PmhQDXoawYvPT1XA1rLBnlciPDV4_myhBigGvi-AcedULlG15fHG0VDmDBv-OjuOAM3l-8d6i7P_WvVppFdDjv2qzVyYit7YHIfEcNkb1rzTd5zubzux_-PVW3RggQqKZy_8IqqnbWadJe3VsyLmM51XepvaUPwxpV6sCLiwlLdkUXNszJyN5qGjw3__AiuJdE_HtQteibsdtH3FUmo3esjYr9jrdGxcvvw8G7ajQLyn0UylNUIpj8gz0GW3JmQo",
        ]);
        $place2 = Place::create([
            'id' => "ChIJ5SdV5RpF0i0RWSeph8s7d5M",
            'name' => "Dreamland Beach",
            'address' => "Jl. Pantai Balangan No.54, Pecatu, Kec. Kuta Sel., Kabupaten Badung, Bali 80361, Indonesia",
            'province' => "Bali",
            'types' => json_encode(["tourist_attraction","point_of_interest","establishment"]),
            "latitude" => -8.7993192,
            "longitude" => 115.1177659,
            "rating" => 4.4,
            "url" => "https://maps.google.com/?cid=10626027591144384345",
            "summary" => "Popular with surfers, this beach on the Bukit peninsula offers simple accommodations & cafes.",
            "photo" => "places/ChIJ5SdV5RpF0i0RWSeph8s7d5M/photos/AZLasHqa9fgRxzV6iux2EiOm0bveQJ4MEfA8YPEShYxu0i9xYWlxCPRrikHjVK-_dDJTPrvJByTtX9FW2qaQRa38rePOXBbw64n5BpSXhZmIkzQL7xf1_dnuO7IDVghjcui8pMivZylDXHKtoMumsZmslILvkHGbh3gxVCU1oPqkEM_rn8MkjWvP3TmfRBFMvha-E_YMVXqt87vaUab_nxOO0B_9ynqx3OtdW6ufYG28CxH8UhBGU21hLWdnMNeoxBra1y-PmwM5eu6P3TV2onS0CYEen5j4Tp9LzS1-pxB9IuhFrF4R4W_0z5o_MZL5vlR2WeP0a6hW39iSR0pFDkGks5rVKN-bSwkDp6CGn09V6akSGTcUAJGbdEndyFG4I8o6jAynDV75gGPO87LX5J4VdtX7XxCRFSOpyLk-AfV8A6kFWx0",
        ]);
        $plan1->activities()->createMany([[
            'place_id' => $place1->id,
            'time' => now()->add(3, 'hours')
        ], [
            'place_id' => $place2->id,
            'time' => now()->add(8, 'hours')
        ]]);

        // $plan2 = Plan::create([
        //     // 'id' => $id1,
        //     'name' => 'Trip to Bali 2',
        //     'start_date' => now()->add(7, 'days'),
        //     'end_date' => now()->add(13, 'days'),
        //     'public_id' => (string) Str::uuid()
        // ]);
        // $plan2->users()->attach($user2->id, ['accepted_at' => now(), 'role' => 'Owner']);
        // $plan2->activities()->create([
        //     'place_id' => $place1->id,
        //     'time' => now()->add(3, 'hours')
        // ]);
    }
}