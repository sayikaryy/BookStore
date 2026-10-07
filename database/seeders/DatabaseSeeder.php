<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Book;
use App\Models\Supplier;
use App\Models\InventoryLog;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ActivityLog;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Roles
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full administrative access']
        );
        $customerRole = Role::updateOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'description' => 'Regular customer access for ordering']
        );

        // 2. Admin User
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

        // 3. Customer Users
        $customer1 = User::updateOrCreate(
            ['email' => 'john@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'phone' => '+855 98 765 432',
                'address' => 'Street 271, Boeng Tumpun, Phnom Penh',
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
                'address' => 'Street 315, Toul Kork, Phnom Penh',
                'role' => 'customer',
                'status' => 'active',
            ]
        );

        // 4. Addresses
        Address::updateOrCreate(
            ['user_id' => $customer1->id, 'label' => 'Home'],
            [
                'recipient_name' => 'John Doe',
                'phone' => '+855 98 765 432',
                'address_line' => '#45, Street 271, Boeng Tumpun',
                'city' => 'Phnom Penh',
                'is_default' => true,
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer1->id, 'label' => 'Office'],
            [
                'recipient_name' => 'John Doe',
                'phone' => '+855 98 765 432',
                'address_line' => 'Canadia Tower Floor 15, Monivong Blvd',
                'city' => 'Phnom Penh',
                'is_default' => false,
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer2->id, 'label' => 'Home'],
            [
                'recipient_name' => 'Sarah Connor',
                'phone' => '+855 88 112 233',
                'address_line' => '#12, Street 315, Toul Kork',
                'city' => 'Phnom Penh',
                'is_default' => true,
            ]
        );

        // 5. Suppliers
        $supplier1 = Supplier::updateOrCreate(
            ['name' => 'Monument Distribution Ltd.'],
            [
                'contact_person' => 'Sokha Meng',
                'email' => 'sokha@monumentbooks.com',
                'phone' => '+855 23 889 001',
                'address' => '#111, Norodom Blvd, Phnom Penh',
                'status' => 'active',
            ]
        );

        $supplier2 = Supplier::updateOrCreate(
            ['name' => 'IBC Wholesale Cambodia'],
            [
                'contact_person' => 'Chanthy Vuthy',
                'email' => 'orders@ibccambodia.com',
                'phone' => '+855 23 428 111',
                'address' => 'Sihanouk Blvd, Phnom Penh',
                'status' => 'active',
            ]
        );

        $supplier3 = Supplier::updateOrCreate(
            ['name' => 'Global Book Importers Pte'],
            [
                'contact_person' => 'David Lee',
                'email' => 'supply@globalbooks.sg',
                'phone' => '+65 6789 0123',
                'address' => 'Robinson Road, Singapore',
                'status' => 'active',
            ]
        );

        // 6. Categories
        $fiction = Category::updateOrCreate(
            ['name' => 'Fiction'],
            ['description' => 'Novels, literary classics, and storytelling', 'status' => 'active']
        );

        $finance = Category::updateOrCreate(
            ['name' => 'Finance & Business'],
            ['description' => 'Personal finance, investing, economics, and leadership', 'status' => 'active']
        );

        $selfHelp = Category::updateOrCreate(
            ['name' => 'Self-Help'],
            ['description' => 'Mindset, productivity, habits, and psychology', 'status' => 'active']
        );

        $tech = Category::updateOrCreate(
            ['name' => 'Technology & Science'],
            ['description' => 'Software engineering, AI, computer science, and data', 'status' => 'active']
        );

        $history = Category::updateOrCreate(
            ['name' => 'History & Biography'],
            ['description' => 'World history, civilizations, memoirs, and biographies', 'status' => 'active']
        );

        // 7. Books Catalog with rich cover images and ratings
        $booksData = [
            [
                'category_id' => $selfHelp->id,
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'isbn' => '9780735211292',
                'publisher' => 'Avery',
                'publication_year' => 2018,
                'description' => 'An Easy & Proven Way to Build Good Habits & Break Bad Ones. No matter your goals, Atomic Habits offers a proven framework for improving every day.',
                'price' => 15.00,
                'stock' => 28,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.90,
                'rating_count' => 142,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'category_id' => $finance->id,
                'title' => 'Rich Dad Poor Dad',
                'author' => 'Robert T. Kiyosaki',
                'isbn' => '9781612680194',
                'publisher' => 'Plata Publishing',
                'publication_year' => 2017,
                'description' => 'What the rich teach their kids about money that the poor and middle class do not! Explodes the myth that you need to earn a high income to be rich.',
                'price' => 18.00,
                'stock' => 15,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1592496431122-2349e0fbc666?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.75,
                'rating_count' => 98,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'category_id' => $tech->id,
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2008,
                'description' => 'A Handbook of Agile Software Craftsmanship. Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.',
                'price' => 35.00,
                'stock' => 12,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.85,
                'rating_count' => 84,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'category_id' => $fiction->id,
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '9780743273565',
                'publisher' => 'Scribner',
                'publication_year' => 1925,
                'description' => 'The exemplary novel of the Jazz Age, telling the dark story of mysterious millionaire Jay Gatsby and his obsessive passion for Daisy Buchanan.',
                'price' => 12.50,
                'stock' => 35,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.60,
                'rating_count' => 67,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'category_id' => $history->id,
                'title' => 'Sapiens: A Brief History of Humankind',
                'author' => 'Yuval Noah Harari',
                'isbn' => '9780062316097',
                'publisher' => 'Harper',
                'publication_year' => 2015,
                'description' => 'Surveys the history of humankind from the evolution of archaic human species in the Stone Age up to twenty-first-century humankind.',
                'price' => 22.00,
                'stock' => 20,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.88,
                'rating_count' => 110,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'category_id' => $finance->id,
                'title' => 'The Psychology of Money',
                'author' => 'Morgan Housel',
                'isbn' => '9780857197689',
                'publisher' => 'Harriman House',
                'publication_year' => 2020,
                'description' => 'Timeless lessons on wealth, greed, and happiness doing well with money isn\'t necessarily about what you know. It\'s about how you behave.',
                'price' => 16.50,
                'stock' => 3, // Low stock for alert testing!
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1553729459-efe14ef6055d?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.92,
                'rating_count' => 135,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'category_id' => $tech->id,
                'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'author' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'isbn' => '9780201633610',
                'publisher' => 'Addison-Wesley',
                'publication_year' => 1994,
                'description' => 'Capturing a wealth of experience about the design of object-oriented software, four top-notch designers present a catalog of simple solutions to common design problems.',
                'price' => 42.00,
                'stock' => 4, // Low stock
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.70,
                'rating_count' => 52,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'category_id' => $selfHelp->id,
                'title' => 'Deep Work: Rules for Focused Success',
                'author' => 'Cal Newport',
                'isbn' => '9781455586691',
                'publisher' => 'Grand Central Publishing',
                'publication_year' => 2016,
                'description' => 'Deep work is the ability to focus without distraction on a cognitively demanding task. It is a skill that allows you to quickly master complicated information.',
                'price' => 17.50,
                'stock' => 19,
                'low_stock_threshold' => 5,
                'cover_image' => 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&q=80&w=600',
                'rating' => 4.80,
                'rating_count' => 78,
                'is_featured' => false,
                'status' => 'active',
            ],
        ];

        $createdBooks = [];
        foreach ($booksData as $bData) {
            $createdBooks[] = Book::updateOrCreate(['isbn' => $bData['isbn']], $bData);
        }

        // 8. Seed Sample Inventory Logs
        foreach ($createdBooks as $book) {
            InventoryLog::updateOrCreate(
                ['book_id' => $book->id, 'type' => 'stock_in'],
                [
                    'supplier_id' => $supplier1->id,
                    'user_id' => $admin->id,
                    'type' => 'stock_in',
                    'quantity' => $book->stock,
                    'previous_stock' => 0,
                    'new_stock' => $book->stock,
                    'unit_cost' => round($book->price * 0.65, 2),
                    'reference_number' => 'PO-' . rand(10000, 99999),
                    'notes' => 'Initial bulk inventory arrival from Monument Distribution',
                ]
            );
        }

        // 9. Orders and Payments with full KHQR / Card details
        $order1 = Order::updateOrCreate(
            ['order_number' => 'ORD-20260925-1001'],
            [
                'user_id' => $customer1->id,
                'subtotal' => 48.00,
                'delivery_fee' => 1.50,
                'discount' => 0.00,
                'total_amount' => 49.50,
                'status' => 'delivered',
                'shipping_address' => '#45, Street 271, Boeng Tumpun, Phnom Penh',
                'delivery_method' => 'Standard Courier (1-2 days)',
                'phone' => '+855 98 765 432',
                'note' => 'Please call upon arrival at front gate.',
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order1->id, 'book_id' => $createdBooks[0]->id],
            [
                'quantity' => 2,
                'price' => 15.00,
                'subtotal' => 30.00,
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order1->id, 'book_id' => $createdBooks[1]->id],
            [
                'quantity' => 1,
                'price' => 18.00,
                'subtotal' => 18.00,
            ]
        );

        Payment::updateOrCreate(
            ['order_id' => $order1->id],
            [
                'transaction_id' => 'TXN-ABA-998822',
                'payment_method' => 'ABA_KHQR',
                'bank_provider' => 'ABA',
                'amount' => 49.50,
                'currency' => 'USD',
                'status' => 'paid',
                'qr_string' => '00020101021229370016bakong@abaa00010108abaa_usd520459995303840540549.505802KH5912BookVerseKH6010Phnom Penh6304E1D2',
                'notes' => 'Settled instantly via ABA Payway KHQR',
                'paid_at' => now()->subDays(2),
            ]
        );

        $order2 = Order::updateOrCreate(
            ['order_number' => 'ORD-20260925-1002'],
            [
                'user_id' => $customer2->id,
                'subtotal' => 35.00,
                'delivery_fee' => 1.50,
                'discount' => 2.00,
                'total_amount' => 34.50,
                'status' => 'processing',
                'shipping_address' => '#12, Street 315, Toul Kork, Phnom Penh',
                'delivery_method' => 'Express Delivery (Same Day)',
                'phone' => '+855 88 112 233',
                'note' => 'Leave package at reception desk.',
            ]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order2->id, 'book_id' => $createdBooks[2]->id],
            [
                'quantity' => 1,
                'price' => 35.00,
                'subtotal' => 35.00,
            ]
        );

        Payment::updateOrCreate(
            ['order_id' => $order2->id],
            [
                'transaction_id' => 'TXN-BK-774411',
                'payment_method' => 'BAKONG_KHQR',
                'bank_provider' => 'BAKONG',
                'amount' => 34.50,
                'currency' => 'USD',
                'status' => 'paid',
                'qr_string' => '00020101021229370016bakong@nbckh0010108nbc_khqr520459995303840540534.505802KH5912BookVerseKH6010Phnom Penh6304A4B1',
                'notes' => 'Settled via NBC Bakong KHQR',
                'paid_at' => now()->subHours(4),
            ]
        );

        // 10. Sample Activity Logs
        ActivityLog::record('login', 'auth', 'Admin signed in to management portal', [], $admin->id);
        ActivityLog::record('stock_in', 'inventory', 'Stock intake of 28 units for Atomic Habits', ['book_id' => $createdBooks[0]->id, 'quantity' => 28], $admin->id);
        ActivityLog::record('order_completed', 'orders', 'Order ORD-20260925-1001 marked as delivered', ['order_id' => $order1->id], $admin->id);
    }
}
