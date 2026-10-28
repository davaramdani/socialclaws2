<?php

namespace Database\Seeders;

use App\Models\Gallery;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed only empty catalog tables; customer accounts and existing content remain intact.
        if (! Service::exists()) {
            foreach ([
                ['Signature Manicure', 'Perawatan kutikula, bentuk kuku, dan gel polish dengan hasil rapi yang terasa seperti dirimu.', 150000, 'images/nail-art.jpg', 60, 'signature'],
                ['Custom Nail Art', 'Dari detail minimal hingga karya penuh warna. Ceritakan idemu, kami wujudkan di setiap ujung jari.', 225000, 'images/nail-detail.jpg', 90, 'nail art'],
                ['Gel Extension', 'Panjang dan bentuk yang kamu inginkan, dengan finishing ringan, kuat, dan nyaman.', 300000, 'images/nail-studio.jpg', 120, 'extension'],
            ] as [$name, $description, $price, $image, $duration, $category]) {
                Service::create(['name' => $name, 'description' => $description, 'price' => $price, 'image' => $image, 'duration_minutes' => $duration, 'category' => $category]);
            }
        }
        if (! Gallery::exists()) {
            foreach ([
                ['Cherry on Top', 'Aksen cherry red untuk tampilan yang berani dan tetap elegan.', 225000, 'images/nail-art.jpg', 'nail art'],
                ['Soft & Sculpted', 'Warna lembut dengan detail modern yang mudah dipadukan dengan gaya harian.', 200000, 'images/nail-detail.jpg', 'minimal'],
                ['Little Details', 'Sentuhan kecil yang menjadikan setiap kuku istimewa.', 250000, 'images/nail-studio.jpg', 'nail art'],
            ] as [$name, $description, $price, $image, $category]) {
                Gallery::create(['name' => $name, 'description' => $description, 'price' => $price, 'image' => $image, 'duration_minutes' => 90, 'category' => $category]);
            }
        }
    }
}
