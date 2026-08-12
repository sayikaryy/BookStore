<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@bookverse.com'],
            [
                'name' => 'BookVerse Admin',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 678',
                'address' => 'Building 123, Monivong Blvd, Phnom Penh',
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 2. Seed Customer Users
        $customer1 = User::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'phone' => '+855 98 765 432',
                'address' => 'Street 271, Phnom Penh',
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        $customer2 = User::updateOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'name' => 'Sarah Connor',
                'password' => Hash::make('password'),
                'phone' => '+855 88 112 233',
                'address' => 'Toul Kork, Phnom Penh',
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        // 3. Seed Categories
        $fiction = Category::updateOrCreate(
            ['name' => 'Fiction'],
            ['description' => 'Fiction books, novels, and literature', 'status' => 'active']
        );

        $finance = Category::updateOrCreate(
            ['name' => 'Finance & Business'],
            ['description' => 'Personal finance, investment, economics, and business strategy', 'status' => 'active']
        );

        $selfHelp = Category::updateOrCreate(
            ['name' => 'Self-Help'],
            ['description' => 'Personal development, psychology, productivity, and habits', 'status' => 'active']
        );

        $tech = Category::updateOrCreate(
            ['name' => 'Technology & Science'],
            ['description' => 'Software development, computer science, AI, and modern tech', 'status' => 'active']
        );

        $history = Category::updateOrCreate(
            ['name' => 'History & Biography'],
            ['description' => 'Historical events, memoirs, world history, and biographies', 'status' => 'active']
        );

        // 4. Seed Books
        $b1 = Book::updateOrCreate(
            ['title' => 'Atomic Habits'],
            [
                'category_id' => $selfHelp->id,
                'author' => 'James Clear',
                'isbn' => '9780735211292',
                'publisher' => 'Avery',
                'publication_year' => 2018,
                'description' => 'An Easy & Proven Way to Build Good Habits & Break Bad Ones.',
                'price' => 15.00,
                'stock' => 25,
                'status' => 'active',
            ]
        );

        $b2 = Book::updateOrCreate(
            ['title' => 'Rich Dad Poor Dad'],
            [
                'category_id' => $finance->id,
                'author' => 'Robert T. Kiyosaki',
                'isbn' => '9781612680194',
                'publisher' => 'Plata Publishing',
                'publication_year' => 2017,
                'description' => 'What the Rich Teach Their Kids About Money That the Poor and Middle Class Do Not!',
                'price' => 18.00,
                'stock' => 14,
                'status' => 'active',
            ]
        );

        $b3 = Book::updateOrCreate(
            ['title' => 'Clean Code'],
            [
                'category_id' => $tech->id,
                'author' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2008,
                'description' => 'A Handbook of Agile Software Craftsmanship.',
                'price' => 35.00,
                'stock' => 10,
                'status' => 'active',
            ]
        );

        $b4 = Book::updateOrCreate(
            ['title' => 'The Great Gatsby'],
            [
                'category_id' => $fiction->id,
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'publisher' => 'Scribner',
                'publication_year' => 1925,
                'description' => 'The classic story of Jay Gatsby and his unrequited love for Daisy Buchanan.',
                'price' => 12.50,
                'stock' => 30,
                'status' => 'active',
            ]
        );

        $b5 = Book::updateOrCreate(
            ['title' => 'Sapiens: A Brief History of Humankind'],
            [
                'category_id' => $history->id,
                'author' => 'Yuval Noah Harari',
                'isbn' => '9780062316097',
                'publisher' => 'Harper',
                'publication_year' => 2015,
                'description' => 'Surveys the history of humankind from the evolution of archaic human species in the Stone Age up to twenty-first-century humankind.',
                'price' => 22.00,
                'stock' => 18,
                'status' => 'active',
            ]
        );

        // 5. Seed Orders & Payments
        if (Order::count() === 0) {
            $order1 = Order::create([
                'user_id' => $customer1->id,
                'total_amount' => 48.00,
                'status' => 'completed',
                'shipping_address' => 'Street 271, Phnom Penh',
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'book_id' => $b1->id,
                'quantity' => 2,
                'price' => 15.00,
                'subtotal' => 30.00,
            ]);

            OrderItem::create([
                'order_id' => $order1->id,
                'book_id' => $b2->id,
                'quantity' => 1,
                'price' => 18.00,
                'subtotal' => 18.00,
            ]);

            Payment::create([
                'order_id' => $order1->id,
                'transaction_id' => 'TXN-ABA-98124',
                'payment_method' => 'ABA_KHQR',
                'amount' => 48.00,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $order2 = Order::create([
                'user_id' => $customer2->id,
                'total_amount' => 35.00,
                'status' => 'completed',
                'shipping_address' => 'Toul Kork, Phnom Penh',
            ]);

            OrderItem::create([
                'order_id' => $order2->id,
                'book_id' => $b3->id,
                'quantity' => 1,
                'price' => 35.00,
                'subtotal' => 35.00,
            ]);

            Payment::create([
                'order_id' => $order2->id,
                'transaction_id' => 'TXN-BAKONG-55412',
                'payment_method' => 'BAKONG',
                'amount' => 35.00,
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }
}
