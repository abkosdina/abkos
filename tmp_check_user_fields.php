<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Chat\Models\ChatMessage;
use App\Models\User;

// Check User table columns
echo "📋 USER TABLE COLUMNS:\n";
echo "─────────────────────────────────────\n";
$user = User::first();
if ($user) {
    echo "Name: " . $user->name . "\n";
    echo "Mobile: " . $user->mobile . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Created At: " . $user->created_at . "\n";
    
    // Check if columns exist
    $attributes = $user->getAttributes();
    echo "\n🔍 Available Fields:\n";
    foreach ($attributes as $key => $value) {
        echo "  - $key\n";
    }
}

echo "\n\n📬 SAMPLE MESSAGE WITH SENDER:\n";
echo "─────────────────────────────────────\n";
$message = ChatMessage::with('sender')->first();
if ($message && $message->sender) {
    echo json_encode([
        'id' => $message->id,
        'message' => $message->message,
        'sender' => [
            'id' => $message->sender->id,
            'name' => $message->sender->name,
            'mobile' => $message->sender->mobile,
            'email' => $message->sender->email,
        ],
        'created_at' => $message->created_at,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
}
