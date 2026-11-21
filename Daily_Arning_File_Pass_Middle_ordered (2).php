<?php
error_reporting(0);
ini_set('max_execution_time', 0);
date_default_timezone_set('Asia/Dhaka');

define('API_KEY', '9035897395:AAGi0T7WJwjjMdh4MltX2XfBUf_97imf7vE'); 
define('ADMIN_ID', '8655385885'); 

$bot_username = "KOP_BUY_SELL_bot";

@mkdir('data');
@mkdir('data/tasks_csv');

$db_files = [
    'users.json' => '{}', 
    'settings.json' => '{}', 
    'proofs.json' => '[]', 
    'withdraws.json' => '[]',
    'withdraw_methods.json' => '[]',
    'seed_cache.json' => '{}',
    'tasks.json' => '[]'
];

foreach ($db_files as $file => $default) { 
    if (!file_exists("data/$file")) {
        file_put_contents("data/$file", $default); 
    }
}

$master_files = [
    'accounts_master.csv' => ["Username", "Password", "2FA Key"],
    'gmail_master.csv' => ["Name", "Email", "Password"],
    'facebook_master.csv' => ["UID", "Password", "2FA Key"], 
    'facebook_cookies_master.csv' => ["UID", "Password", "Cookies"],
    'instagram_cookies_master.csv' => ["Username", "Password", "Cookies"] 
];

foreach ($master_files as $csv_name => $headers) {
    if (!file_exists("data/$csv_name")) {
        $file = fopen("data/$csv_name", "w");
        fputcsv($file, $headers);
        fclose($file);
    }
}

function pe($emoji, $id) {
    return '<tg-emoji emoji-id="' . $id . '">' . $emoji . '</tg-emoji>';
}

function t($key, $lang = 'bn') {
    $dict = [
        'welcome' => ['bn' => "স্বাগতম! কাজ শুরু করতে নিচের অপশনগুলো ব্যবহার করুন ", 'en' => "Welcome! Use the options below to get started "],
        'joined_success' => ['bn' => "স্বাগতম! আপনি সফলভাবে জয়েন করেছেন।", 'en' => "Welcome! You have successfully joined."],
        'must_join_title' => ['bn' => "হ্যালো", 'en' => "Hello"],
        'must_join_body' => ['bn' => "বটটি ব্যবহার করতে আপনাকে অবশ্যই নিচের সবগুলো চ্যানেলে জয়েন করতে হবে। জয়েন করে নিচের 'জয়েন করেছি' বাটনে ক্লিক করুন।", 'en' => "You must join all the channels below to use this bot. After joining, tap the 'I have Joined' button below."],
        'joined_btn' => ['bn' => "✅ জয়েন করেছি", 'en' => "✅ I have Joined"],
        'not_joined_alert' => ['bn' => "❌ আপনি এখনো সবগুলো চ্যানেলে জয়েন করেননি!", 'en' => "❌ You haven't joined all the channels yet!"],
        'language_prompt' => ['bn' => "আপনার পছন্দের ভাষা বেছে নিন:", 'en' => "Choose your preferred language:"],
        'language_set_bn' => ['bn' => "✅ ভাষা বাংলায় পরিবর্তন করা হয়েছে!", 'en' => "✅ Language changed to বাংলা (Bangla)!"],
        'language_set_en' => ['bn' => "✅ ভাষা ইংরেজিতে পরিবর্তন করা হয়েছে!", 'en' => "✅ Language changed to English!"],
        'job_arena_prompt' => ['bn' => "নিচের তালিকা থেকে একটি কাজ সিলেক্ট করুন:", 'en' => "Select a task from the list below:"],
        'insta_2fa_label' => [ 'bn' => "ইনস্টা 2FA", 'en' => "Insta 2FA" ],
        'gmail_label' => [ 'bn' => "জিমেইল কাজ", 'en' => "Gmail Task" ],
        'fb_2fa_label' => [ 'bn' => "ফেসবুক 2FA", 'en' => "Facebook 2FA" ],
        'fb_cookies_label' => [ 'bn' => "ফেসবুক কুকিজ", 'en' => "Facebook Cookies" ],
        'insta_cookies_label' => [ 'bn' => "ইনস্টা কুকিজ", 'en' => "Insta Cookies" ],
        'cancel_label' => [ 'bn' => "বাতিল", 'en' => "Cancel" ]
    ];
    if (!isset($dict[$key])) return $key;
    return $dict[$key][$lang] ?? $dict[$key]['bn'];
}

function L($lang, $bn, $en) {
    return ($lang === 'en') ? $en : $bn;
}

function readDB($filename) { 
    $content = @file_get_contents("data/$filename");
    if (empty($content)) return [];
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function writeDB($filename, $data) { 
    $result = @file_put_contents("data/$filename", json_encode($data, JSON_PRETTY_PRINT));
    return $result !== false;
}

function setState($uid, $newState, $newTemp = '') {
    $users = readDB('users.json');
    if (isset($users[$uid])) {
        $users[$uid]['state'] = $newState;
        $users[$uid]['temp'] = $newTemp;
        writeDB('users.json', $users);
    }
}

$s = readDB('settings.json');
$defaults = [
    'force_channel' => 'https://t.me/Jx_prime1', 
    'force_channel_url' => 'https://t.me/Jx_prime1',
    'support_id' => 'https://t.me/Daily_Arning_Support',
    'official_channel' => 'https://t.me/Jx_prime1',
    'ref_commission' => 5, 
    'min_wd_bkash' => 100,
    'bkash_charge' => 5,
    'min_wd_nagad' => 100,
    'nagad_charge' => 5,
    'min_wd_rocket' => 100, 
    'rocket_charge' => 5,   
    'status_wd_bkash' => 'open',  
    'status_wd_nagad' => 'open',  
    'status_wd_rocket' => 'open', 
    'reward_instagram' => 4.00,
    'reward_gmail' => 5.00,
    'reward_facebook' => 6.00,
    'reward_facebook_cookies' => 8.00, 
    'reward_instagram_cookies' => 8.00, 
    'video_instagram' => '',
    'admin_insta_password' => 'Pass@insta',   
    'admin_gmail_password' => 'Pass@gmail',   
    'admin_fb_password' => 'Pass@facebook',   
    'admin_fb_cookies_password' => 'Pass@cookies', 
    'admin_insta_cookies_password' => 'Pass@instacookies',   
    'bot_username' => '', 
    'work_videos' => ['https://youtube.com'],
    'status_instagram' => 'open',
    'status_gmail' => 'open',
    'status_facebook' => 'open',
    'status_facebook_cookies' => 'open', 
    'status_instagram_cookies' => 'open',
    'seed_instagram_url' => '',
    'seed_instagram_sheet' => 'Instagram',
    'seed_instagram_status' => 'off',
    'seed_fbcookies_url' => '',
    'seed_fbcookies_sheet' => 'FBCookies',
    'seed_fbcookies_status' => 'off',
    'seed_gmail_url' => '',
    'seed_gmail_sheet' => 'Gmail',
    'seed_gmail_status' => 'off',
    'backup_status' => 'off',
    'backup_interval' => 6,
    'backup_last_time' => 0,
    'backup_auto_send' => 'on'
];
$updated = false; 
foreach ($defaults as $k => $v) { 
    if (!isset($s[$k])) { $s[$k] = $v; $updated = true; } 
}
if ($updated) writeDB('settings.json', $s);

const PREMIUM_EMOJI_MAP = [
    '📥' => '5406745015365943482',
    '🧩' => '5406745015365943482',
    '📢' => '5424818078833715060',
    '🎉' => '5461151367559141950',
    '⚠️' => '5447644880824181073',
    '🙂' => '5461117441612462242',
    '⬇️' => '5406745015365943482',
    '🌐' => '5253742260054409879',
    '🎯' => '5224607267797606837',
    '🟢' => '5416081784641168838',
    '🔑' => '5296369303661067030',
    '⤵️' => '5447183459602669338',
    '❌' => '5210952531676504517',
    '🔐' => '5296369303661067030',
    '🔔' => '5458603043203327669',
    '🛡' => '5251203410396458957',
    '⌛' => '5386367538735104399',
    '✅' => '5206607081334906820',
    '📧' => '5253742260054409879',
    '📌' => '5397782960512444700',
    '⏳' => '5386367538735104399',
    '👥' => '5460795800101594035',
    '🆔' => '5231012545799666522',
    '🍪' => '5229064374403998351',
    '📸' => '5210956306952758910',
    '🎬' => '5264919878082509254',
    '🔗' => '5271604874419647061',
    '💵' => '5409048419211682843',
    '💰' => '5233326571099534068',
    '🔒' => '5296369303661067030',
    '💎' => '5427168083074628963',
    '📱' => '5231012545799666522',
    '🚀' => '5456140674028019486',
    '👤' => '5460795800101594035',
    '💲' => '5409048419211682843',
    'ℹ️' => '5334544901428229844',
    '💬' => '5443038326535759644',
    '⚙️' => '5341715473882955310',
    '📊' => '5231200819986047254',
    '🔍' => '5231012545799666522',
    '🎨' => '5253742260054409879',
    '🧹' => '5445267414562389170',
    '📝' => '5253742260054409879',
    '✏️' => '5253742260054409879',
    '➕' => '5397916757333654639',
    '➖' => '5447183459602669338',
    '🗑️' => '4958534924278694938',
    '✔️' => '4956721670690702265',
    '⚡' => '4958479549265347295',
    '💸' => '4958869558065627840',
    '🆕' => '5397916757333654639',
    '🖥️' => '5397916757333654639',
];

const BUTTON_ICON_OVERRIDE = [
    'Admin Dashboard' => '5341715473882955310',
    'Bot Settings' => '5341715473882955310',
    'Payment On/Off' => '5341715473882955310',
    'Task On/Off' => '5341715473882955310',
    'কাজ চালু/বন্ধ' => '5341715473882955310',
    'পেমেন্ট অন/অফ' => '5341715473882955310',
    'বটের সেটিংস' => '5341715473882955310',
    'সিড কন্ট্রোল' => '5231200819986047254',
    'ব্যাকআপ' => '5406745015365943482',
    'কাজ ▸' => '6221907306813069910',
    'কাজ ম্যানেজমেন্ট' => '5397916757333654639',
    'Task Management' => '5397916757333654639',
    'Complete Cookies Submission' => '5206607081334906820',
    'Complete Submission' => '5206607081334906820',
    'Cookies Good ID' => '5206607081334906820',
    'Facebook Good ID' => '5206607081334906820',
    'Gmail Good ID' => '5206607081334906820',
    'Insta Cookies Good ID' => '5206607081334906820',
    'Insta Good ID' => '5206607081334906820',
    'Job Done' => '5206607081334906820',
    'ইনস্টা কুকিজ ভালো আইডি' => '5206607081334906820',
    'ইনস্টা ভালো আইডি' => '5206607081334906820',
    'কাজ শেষ' => '5206607081334906820',
    'কুকিজ ভালো আইডি' => '5206607081334906820',
    'কুকিজ সাবমিট সম্পূর্ণ করুন' => '5206607081334906820',
    'জিমেইল ভালো আইডি' => '5206607081334906820',
    'ফেসবুক ভালো আইডি' => '5206607081334906820',
    'সাবমিট সম্পূর্ণ করুন' => '5206607081334906820',
    'Cancel' => '5210952531676504517',
    'Cookies Bad ID' => '5210952531676504517',
    'Delete All Videos' => '5210952531676504517',
    'Facebook Bad ID' => '5210952531676504517',
    'Gmail Bad ID' => '5210952531676504517',
    'Insta Bad ID' => '5210952531676504517',
    'Insta Cookies Bad ID' => '5210952531676504517',
    'ইনস্টা কুকিজ খারাপ আইডি' => '5210952531676504517',
    'ইনস্টা খারাপ আইডি' => '5210952531676504517',
    'কুকিজ খারাপ আইডি' => '5210952531676504517',
    'জিমেইল খারাপ আইডি' => '5210952531676504517',
    'ফেসবুক খারাপ আইডি' => '5210952531676504517',
    'বাতিল' => '5210952531676504517',
    'সব ভিডিও মুছুন' => '5210952531676504517',
    'Add New Channel' => '5397916757333654639',
    'Add New Method' => '5397916757333654639',
    'Add New Task' => '5397916757333654639',
    'Add Video' => '5397916757333654639',
    'নতুন চ্যানেল যোগ করুন' => '5397916757333654639',
    'নতুন মেথড যোগ করুন' => '5397916757333654639',
    'নতুন কাজ যোগ করুন' => '5397916757333654639',
    'ভিডিও যোগ করুন' => '5397916757333654639',
    'Language / ভাষা' => '5253742260054409879',
    'Join Channel Management' => '5253742260054409879',
    'জয়েন চ্যানেল ম্যানেজমেন্ট' => '5253742260054409879',
    'আমি নতুন' => '5264919878082509254',
    'Cookies Control' => '5460795800101594035',
    'Facebook Control' => '5460795800101594035',
    'কুকিজ কন্ট্রোল' => '5460795800101594035',
    'ফেসবুক কন্ট্রোল' => '5460795800101594035',
    'রেফার কমিশন সেট' => '5460795800101594035',
    'ব্যালেন্স' => '6318616479826715485',
    'সাপোর্ট' => '5253507424127557691',
    'Cookies Task Price' => '5233326571099534068',
    'Facebook Task Price' => '5233326571099534068',
    'Gmail Task Price' => '5233326571099534068',
    'Insta Cookies Task Price' => '5233326571099534068',
    'Insta Task Price' => '5233326571099534068',
    'ইনস্টা কাজের মূল্য' => '5233326571099534068',
    'ইনস্টা কুকিজ কাজের মূল্য' => '5233326571099534068',
    'কাজের মূল্য সেট' => '5233326571099534068',
    'কুকিজ কাজের মূল্য' => '5233326571099534068',
    'জিমেইল কাজের মূল্য' => '5233326571099534068',
    'ফেসবুক কাজের মূল্য' => '5233326571099534068',
    'টাকা উত্তোলন' => '5039789890133296083',
    'Central Sheet' => '5231200819986047254',
    'Statistics' => '5231200819986047254',
    'পরিসংখ্যান' => '5231200819986047254',
    'সেন্ট্রাল শিট' => '5231200819986047254',
    'Broadcast' => '5424818078833715060',
    'Force Username' => '5424818078833715060',
    'চ্যানেল সেটিংস' => '5424818078833715060',
    'ফোর্স ইউজারনেম' => '5424818078833715060',
    'ব্রডকাস্ট' => '5424818078833715060',
    'Download Cookies Sheet' => '5406745015365943482',
    'Download Facebook Sheet' => '5406745015365943482',
    'Download Gmail Sheet' => '5406745015365943482',
    'Download Insta Cookies Sheet' => '5406745015365943482',
    'Download Instagram Sheet' => '5406745015365943482',
    'Pending Withdraw' => '5406745015365943482',
    'ইনস্টা কুকিজ শিট ডাউনলোড' => '5406745015365943482',
    'ইনস্টাগ্রাম শিট ডাউনলোড' => '5406745015365943482',
    'কুকিজ শিট ডাউনলোড' => '5406745015365943482',
    'জিমেইল শিট ডাউনলোড' => '5406745015365943482',
    'পেন্ডিং উইথড্র' => '5406745015365943482',
    'ফেসবুক শিট ডাউনলোড' => '5406745015365943482',
    'Gmail Control' => '5253742260054409879',
    'জিমেইল কন্ট্রোল' => '5253742260054409879',
    'Nagad Charge' => '5231012545799666522',
    'Nagad Limit' => '5231012545799666522',
    'Rocket Charge' => '5231012545799666522',
    'Rocket Limit' => '5231012545799666522',
    'bKash Charge' => '5231012545799666522',
    'bKash Limit' => '5231012545799666522',
    'নগদ চার্জ' => '5231012545799666522',
    'নগদ লিমিট' => '5231012545799666522',
    'বিকাশ চার্জ' => '5231012545799666522',
    'বিকাশ লিমিট' => '5231012545799666522',
    'রকেট চার্জ' => '5231012545799666522',
    'রকেট লিমিট' => '5231012545799666522',
    'লিমিট ও চার্জ সেট' => '5231012545799666522',
    'Insta Cookies Control' => '5210956306952758910',
    'Instagram Control' => '5210956306952758910',
    'ইনস্টা কুকিজ কন্ট্রোল' => '5210956306952758910',
    'ইনস্টাগ্রাম কন্ট্রোল' => '5210956306952758910',
    'User Search' => '5231012545799666522',
    'ইউজার সার্চ' => '5231012545799666522',
    '2FA Set' => '5296369303661067030',
    'Cookies Password' => '5296369303661067030',
    'Facebook Password' => '5296369303661067030',
    'Gmail Password' => '5296369303661067030',
    'Insta Cookies Password' => '5296369303661067030',
    'Insta Password' => '5296369303661067030',
    'ইনস্টা কুকিজ পাসওয়ার্ড' => '5296369303661067030',
    'ইনস্টা পাসওয়ার্ড' => '5296369303661067030',
    'কুকিজ পাসওয়ার্ড' => '5296369303661067030',
    'জিমেইল পাসওয়ার্ড' => '5296369303661067030',
    'পাসওয়ার্ড সেটিংস' => '5296369303661067030',
    'ফেসবুক পাসওয়ার্ড' => '5296369303661067030',
    'Official Link' => '5271604874419647061',
    'অফিশিয়াল লিংক' => '5271604874419647061',
    'My Referrals' => '5039823300683891773',
    'উইথড্র মেথড সেটিংস' => '5406745015365943482',
    'Database Cleanup' => '5445267414562389170',
    'ডাটাবেজ ক্লিনআপ' => '5445267414562389170',
    'জিমেইল কাজ' => '5303416490295304868',
    'ইনস্টা 2FA' => '5319160079465857105',
    'ফেসবুক 2FA' => '6118281479811238966',
    'ফেসবুক কুকিজ' => '6118281479811238966',
    'ইনস্টা কুকিজ' => '5319160079465857105',
];

function applyPremiumEmojiToButton($btn, $stripText = true) {
    if (!isset($btn['text']) || !is_string($btn['text']) || $btn['text'] === '') return $btn;
    static $sortedOverrideKeys = null;
    if ($sortedOverrideKeys === null) {
        $sortedOverrideKeys = array_keys(BUTTON_ICON_OVERRIDE);
        usort($sortedOverrideKeys, function($a, $b) { return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8'); });
    }
    foreach ($sortedOverrideKeys as $needle) {
        if (mb_strpos($btn['text'], $needle, 0, 'UTF-8') !== false) {
            $btn['icon_custom_emoji_id'] = BUTTON_ICON_OVERRIDE[$needle];
            if ($stripText) { $btn['text'] = premiumStripLeadingEmoji($btn['text']); }
            return $btn;
        }
    }
    static $sortedKeys = null;
    if ($sortedKeys === null) {
        $sortedKeys = array_keys(PREMIUM_EMOJI_MAP);
        usort($sortedKeys, function($a, $b) { return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8'); });
    }
    foreach ($sortedKeys as $emoji) {
        if (strpos($btn['text'], $emoji) === 0) {
            $btn['icon_custom_emoji_id'] = PREMIUM_EMOJI_MAP[$emoji];
            if ($stripText) {
                $rest = mb_substr($btn['text'], mb_strlen($emoji, 'UTF-8'), null, 'UTF-8');
                $btn['text'] = ltrim($rest);
            }
            break;
        }
    }
    return $btn;
}

function premiumStripLeadingEmoji($text) {
    static $sortedKeys = null;
    if ($sortedKeys === null) {
        $sortedKeys = array_keys(PREMIUM_EMOJI_MAP);
        usort($sortedKeys, function($a, $b) { return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8'); });
    }
    foreach ($sortedKeys as $emoji) {
        if (strpos($text, $emoji) === 0) {
            $rest = mb_substr($text, mb_strlen($emoji, 'UTF-8'), null, 'UTF-8');
            return ltrim($rest);
        }
    }
    return $text;
}

function applyPremiumEmojiToMarkup($markup) {
    if (!is_array($markup)) return $markup;
    foreach (['keyboard' => false, 'inline_keyboard' => true] as $key => $stripText) {
        if (!isset($markup[$key]) || !is_array($markup[$key])) continue;
        foreach ($markup[$key] as &$row) {
            if (!is_array($row)) continue;
            foreach ($row as &$btn) {
                if (is_array($btn)) {
                    $btn = applyPremiumEmojiToButton($btn, $stripText);
                } elseif (is_string($btn)) {
                    $converted = applyPremiumEmojiToButton(['text' => $btn], $stripText);
                    if (isset($converted['icon_custom_emoji_id'])) { $btn = $converted; }
                }
            }
            unset($btn);
        }
        unset($row);
    }
    return $markup;
}

function autoWrapPremiumEmojiInText($text) {
    if (!is_string($text) || $text === '') return $text;
    $protected = [];
    $placeholder = "PROTECTED_%d";
    $i = 0;
    $text = preg_replace_callback('/<tg-emoji[^>]*>.*?<\/tg-emoji>/su', function($m) use (&$protected, &$i, $placeholder) {
        $key = sprintf($placeholder, $i++);
        $protected[$key] = $m[0];
        return $key;
    }, $text);
    static $sortedKeys = null;
    if ($sortedKeys === null) {
        $sortedKeys = array_keys(PREMIUM_EMOJI_MAP);
        usort($sortedKeys, function($a, $b) { return mb_strlen($b, 'UTF-8') - mb_strlen($a, 'UTF-8'); });
    }
    foreach ($sortedKeys as $emoji) {
        $id = PREMIUM_EMOJI_MAP[$emoji];
        $text = str_replace($emoji, '<tg-emoji emoji-id="' . $id . '">' . $emoji . '</tg-emoji>', $text);
    }
    foreach ($protected as $key => $original) {
        $text = str_replace($key, $original, $text);
    }
    return $text;
}

function seedPushToSheet($url, $sheetName, $rowData) {
    if (empty($url) || empty($sheetName) || empty($rowData)) {
        return ['ok' => false, 'error' => 'Missing params'];
    }
    $payload = json_encode(['sheet' => $sheetName, 'row' => $rowData]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return ['ok' => false, 'error' => $err];
    $decoded = json_decode($res, true);
    if (is_array($decoded) && isset($decoded['ok'])) return $decoded;
    return ['ok' => false, 'error' => 'Invalid response: ' . substr($res, 0, 200)];
}

function seedIsDuplicate($seedKey, $uniqueValue) {
    if (empty($uniqueValue)) return false;
    $cache = readDB('seed_cache.json');
    $key = $seedKey . ':' . strtolower(trim($uniqueValue));
    return isset($cache[$key]);
}

function seedMarkDuplicate($seedKey, $uniqueValue) {
    if (empty($uniqueValue)) return;
    $cache = readDB('seed_cache.json');
    $key = $seedKey . ':' . strtolower(trim($uniqueValue));
    $cache[$key] = time();
    writeDB('seed_cache.json', $cache);
}

function seedSubmitTask($s, $task_type, $data) {
    if (!file_exists('data/seed_cache.json')) { file_put_contents('data/seed_cache.json', '{}'); }
    if ($task_type === 'instagram') {
        if (($s['seed_instagram_status'] ?? 'off') !== 'on') return false;
        if (empty($s['seed_instagram_url'])) return false;
        $username = $data['username'] ?? '';
        if (seedIsDuplicate('insta', $username)) return false;
        $row = [$username, $data['password'] ?? '', $data['2fa'] ?? ''];
        $res = seedPushToSheet($s['seed_instagram_url'], $s['seed_instagram_sheet'] ?? 'Instagram', $row);
        if ($res['ok'] ?? false) { seedMarkDuplicate('insta', $username); return true; }
        return false;
    }
    if ($task_type === 'facebook_cookies') {
        if (($s['seed_fbcookies_status'] ?? 'off') !== 'on') return false;
        if (empty($s['seed_fbcookies_url'])) return false;
        $uid = $data['uid'] ?? '';
        if (seedIsDuplicate('fbcookies', $uid)) return false;
        $row = [$uid, $data['password'] ?? '', $data['cookies'] ?? ''];
        $res = seedPushToSheet($s['seed_fbcookies_url'], $s['seed_fbcookies_sheet'] ?? 'FBCookies', $row);
        if ($res['ok'] ?? false) { seedMarkDuplicate('fbcookies', $uid); return true; }
        return false;
    }
    if ($task_type === 'gmail') {
        if (($s['seed_gmail_status'] ?? 'off') !== 'on') return false;
        if (empty($s['seed_gmail_url'])) return false;
        $email = $data['email'] ?? '';
        if (seedIsDuplicate('gmail', $email)) return false;
        $row = [$email, $data['password'] ?? ''];
        $res = seedPushToSheet($s['seed_gmail_url'], $s['seed_gmail_sheet'] ?? 'Gmail', $row);
        if ($res['ok'] ?? false) { seedMarkDuplicate('gmail', $email); return true; }
        return false;
    }
    return false;
}

function createBackupZip() {
    if (!class_exists('ZipArchive')) {
        return ['ok' => false, 'error' => 'ZipArchive not available on this server'];
    }
    $backupDir = 'data/backups';
    if (!is_dir($backupDir)) @mkdir($backupDir, 0755, true);
    $zipName = 'bot_backup_' . date('Y-m-d_H-i-s') . '.zip';
    $zipPath = $backupDir . '/' . $zipName;
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return ['ok' => false, 'error' => 'Cannot create zip'];
    }
    $files = glob('data/*');
    $added = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $localName = basename($file);
            if (strpos($localName, 'bot_backup_') === 0) continue;
            $zip->addFile($file, $localName);
            $added++;
        }
    }
    if (!file_exists('data/seed_cache.json')) {
        $zip->addFromString('seed_cache.json', '{}');
        $added++;
    }
    $zip->close();
    if ($added === 0) {
        @unlink($zipPath);
        return ['ok' => false, 'error' => 'No files to backup'];
    }
    return ['ok' => true, 'path' => $zipPath, 'name' => $zipName, 'files' => $added, 'size' => filesize($zipPath)];
}

function restoreBackupZip($zipFilePath) {
    if (!class_exists('ZipArchive')) {
        return ['ok' => false, 'error' => 'ZipArchive not available'];
    }
    $zip = new ZipArchive();
    if ($zip->open($zipFilePath) !== true) {
        return ['ok' => false, 'error' => 'Cannot open zip'];
    }
    $safetyDir = 'data/pre_restore_' . date('Y-m-d_H-i-s');
    @mkdir($safetyDir, 0755, true);
    foreach (glob('data/*') as $f) {
        if (is_file($f)) @copy($f, $safetyDir . '/' . basename($f));
    }
    $extracted = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        if (strpos($entry, '..') !== false || strpos($entry, '/') !== false) continue;
        $content = $zip->getFromIndex($i);
        if ($content !== false) {
            file_put_contents('data/' . $entry, $content);
            $extracted++;
        }
    }
    $zip->close();
    return ['ok' => true, 'extracted' => $extracted, 'safety_dir' => $safetyDir];
}

function checkAutoBackup($s, $admin_id) {
    if (($s['backup_status'] ?? 'off') !== 'on') return;
    if (($s['backup_auto_send'] ?? 'on') !== 'on') return;
    $interval = intval($s['backup_interval'] ?? 6);
    if ($interval < 1) $interval = 6;
    $lastTime = intval($s['backup_last_time'] ?? 0);
    $now = time();
    $intervalSeconds = $interval * 3600;
    if (($now - $lastTime) < $intervalSeconds) return;
    $result = createBackupZip();
    if (!($result['ok'] ?? false)) return;
    $sendRes = bot('sendDocument', [
        'chat_id' => $admin_id,
        'document' => new CURLFile($result['path']),
        'caption' => "💾 <b>অটো ব্যাকআপ সম্পন্ন!</b>\n\n📦 ফাইল: <code>{$result['name']}</code>\n📊 ফাইল সংখ্যা: {$result['files']}\n📏 সাইজ: " . round($result['size'] / 1024, 2) . " KB\n🕒 সময়: " . date('Y-m-d H:i:s'),
        'parse_mode' => 'HTML'
    ]);
    if ($sendRes['ok'] ?? false) {
        @unlink($result['path']);
        $s['backup_last_time'] = $now;
        writeDB('settings.json', $s);
    }
}

function bot($method, $datas = []) {
    if (isset($datas['reply_markup']) && is_string($datas['reply_markup'])) {
        $decoded_markup = json_decode($datas['reply_markup'], true);
        if (is_array($decoded_markup)) {
            $decoded_markup = applyPremiumEmojiToMarkup($decoded_markup);
            $datas['reply_markup'] = json_encode($decoded_markup);
        }
    }
    if (isset($datas['text']) && is_string($datas['text']) && isset($datas['parse_mode']) && $datas['parse_mode'] === 'HTML') {
        $datas['text'] = autoWrapPremiumEmojiInText($datas['text']);
    }
    if (isset($datas['caption']) && is_string($datas['caption']) && isset($datas['parse_mode']) && $datas['parse_mode'] === 'HTML') {
        $datas['caption'] = autoWrapPremiumEmojiInText($datas['caption']);
    }
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $res = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $res = curl_exec($ch);
        curl_close($ch);
    }

    if ($res === false || $res === '' || $res === null) {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($datas),
                'timeout' => 30
            ]
        ];
        $context = stream_context_create($options);
        $res = @file_get_contents($url, false, $context);
    }

    $decoded = json_decode((string)$res, true);
    return is_array($decoded) ? $decoded : ['ok' => false, 'description' => 'Telegram API request failed or returned invalid JSON.'];
}

if (empty($s['bot_username'])) {
    $me = bot('getMe');
    if ($me['ok']) {
        $s['bot_username'] = $me['result']['username'];
        writeDB('settings.json', $s);
    }
}
$bot_username = $s['bot_username'] ?? "tesfghjtbot";

function rowContainsValue($row, $target) {
    $target = strtolower(trim($target));
    if (empty($target)) return false;
    foreach ($row as $cell) {
        $cell = strtolower(trim($cell));
        if ($cell === $target) return true;
        if (strpos($cell, $target) !== false) {
            if (ctype_digit($target)) {
                if (preg_match('/(?<!\d)' . preg_quote($target, '/') . '(?!\d)/', $cell)) {
                    return true;
                }
            } else {
                return true;
            }
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $current_url = 'https://' . $host . explode('?', $uri)[0];

    if (isset($_GET['setup']) || $action === 'set') {
        $res = bot('setWebhook', ['url' => $current_url]);
        $msg = 'Webhook Set Attempt: ' . (!empty($res['ok']) ? 'SUCCESS ✅' : 'FAILED ❌') . "<br><pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    } elseif ($action === 'delete') {
        $res = bot('deleteWebhook');
        $msg = 'Webhook Delete Attempt: ' . (!empty($res['ok']) ? 'SUCCESS ✅' : 'FAILED ❌') . "<br><pre>" . htmlspecialchars(json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
    } else {
        $msg = '';
    }

    $info = bot('getWebhookInfo');
    $info_data = $info['result'] ?? [];
    $webhook_status = !empty($info_data['url']) ? 'Active ✅' : 'Inactive ❌';
    $pending_updates = $info_data['pending_update_count'] ?? 0;
    $status_class = !empty($info_data['url']) ? 'status-active' : 'status-inactive';
    $safe_bot_username = htmlspecialchars($bot_username, ENT_QUOTES, 'UTF-8');
    $safe_current_url = htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8');

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Telegram Webhook Control Center</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#f4f6f9;color:#333;margin:0;padding:15px;display:flex;justify-content:center;align-items:center;min-height:100vh}
.card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.05);max-width:480px;width:100%;box-sizing:border-box}
h2{margin-top:0;color:#1e293b;border-bottom:2px solid #f1f5f9;padding-bottom:10px;font-size:20px;text-align:center}
.status-badge{display:inline-block;padding:6px 14px;font-size:13px;font-weight:bold;border-radius:20px;background:#e2e8f0;color:#475569}
.status-active{background:#dcfce7;color:#15803d}.status-inactive{background:#fee2e2;color:#b91c1c}
.info-row{margin:12px 0;font-size:13px;display:flex;justify-content:space-between;border-bottom:1px dotted #f1f5f9;padding-bottom:8px}
.info-label{font-weight:600;color:#64748b}.info-value{color:#0f172a;word-break:break-all;text-align:right;max-width:60%;font-family:monospace}
.btn-group{display:flex;gap:8px;margin-top:25px}.btn{flex:1;padding:12px;font-size:13px;font-weight:600;text-align:center;text-decoration:none;border-radius:8px}.btn-primary{background:#2563eb;color:#fff}.btn-danger{background:#dc2626;color:#fff}.btn-secondary{background:#64748b;color:#fff}
.alert{margin-top:15px;padding:12px;border-radius:6px;font-size:12px;background:#eff6ff;color:#1e40af;border-left:4px solid #2563eb;word-break:break-all}
</style>
</head>
<body>
<div class="card">
<h2>🌐 Webhook Control Center</h2>
<div style="text-align:center;margin-bottom:15px"><span class="status-badge {$status_class}">Webhook: {$webhook_status}</span></div>
<div class="info-row"><span class="info-label">Bot Username:</span><span class="info-value">@{$safe_bot_username}</span></div>
<div class="info-row"><span class="info-label">Detected URL:</span><span class="info-value" style="font-size:11px">{$safe_current_url}</span></div>
<div class="info-row"><span class="info-label">Pending Updates:</span><span class="info-value">{$pending_updates}</span></div>
HTML;

    if ($msg !== '') {
        echo '<div class="alert">' . $msg . '</div>';
    }

    echo '<div class="btn-group">'
        . '<a href="?action=set" class="btn btn-primary">⚡ Webhook Set</a>'
        . '<a href="?" class="btn btn-secondary">🔄 Refresh</a>'
        . '<a href="?action=delete" class="btn btn-danger">❌ Delete</a>'
        . '</div></div></body></html>';
    exit;
}

function generateUsername() {
    $first_names = ['rahim', 'karim', 'sajal', 'arif', 'tanvir', 'shakil', 'rony', 'mim', 'sultana', 'rifat', 'tamim', 'hasan', 'anik', 'joy', 'fahim', 'rubel', 'sumon', 'emran', 'nabil', 'sakib', 'faisal', 'ismail', 'sabbir', 'kamal', 'jamal', 'babul', 'sohel'];
    $last_names = ['khan', 'ahmed', 'hossain', 'ali', 'islam', 'rahman', 'chowdhury', 'bhuiyan', 'shaikh', 'miah', 'talukder', 'patwary', 'sarker', 'mozumder'];
    $fn = $first_names[array_rand($first_names)];
    $ln = $last_names[array_rand($last_names)];
    $num = rand(100, 9999);
    return $fn . "_" . $ln . $num;
}

function generateFbNameArray() {
    $first_names = ['rahim', 'karim', 'sajal', 'arif', 'tanvir', 'shakil', 'rony', 'mim', 'sultana', 'rifat', 'tamim', 'hasan', 'anik', 'joy', 'fahim', 'rubel', 'sumon', 'emran', 'nabil', 'sakib', 'faisal', 'ismail', 'sabbir', 'kamal', 'jamal', 'babul', 'sohel'];
    $last_names = ['khan', 'ahmed', 'hossain', 'ali', 'islam', 'rahman', 'chowdhury', 'bhuiyan', 'shaikh', 'miah', 'talukder', 'patwary', 'sarker', 'mozumder'];
    $fn = ucfirst($first_names[array_rand($first_names)]);
    $ln = ucfirst($last_names[array_rand($last_names)]);
    return ['first' => $fn, 'last' => $ln];
}

function generateGmailData() {
    $first_names = ['rahim', 'karim', 'sajal', 'arif', 'tanvir', 'shakil', 'rony', 'mim', 'sultana', 'rifat', 'tamim', 'hasan', 'anik', 'joy', 'fahim', 'rubel', 'sumon', 'emran', 'nabil', 'sakib', 'faisal', 'ismail', 'sabbir', 'kamal'];
    $last_names = ['khan', 'ahmed', 'hossain', 'ali', 'islam', 'rahman', 'chowdhury', 'bhuiyan', 'shaikh'];
    $fn = ucfirst($first_names[array_rand($first_names)]);
    $ln = ucfirst($last_names[array_rand($last_names)]);
    $num = rand(100, 9999);
    return ['name' => $fn . " " . $ln, 'email' => strtolower($fn) . strtolower($ln) . $num . "@gmail.com"];
}

function isValidBase32Key($secret) {
    $secret = str_replace(' ', '', $secret);
    $len = strlen($secret);
    if ($len != 16 && $len != 24 && $len != 32) return false;
    if (preg_match('/[^A-Z2-7]/i', $secret)) return false;
    return true;
}

function base32_decode($secret) {
    $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
    if (empty($secret)) return '';
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split($secret) as $char) {
        $pos = strpos($alphabet, $char);
        if ($pos === false) continue;
        $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }
    $bytes = str_split($binary, 8);
    $out = '';
    foreach ($bytes as $byte) {
        if (strlen($byte) === 8) { $out .= chr(bindec($byte)); }
    }
    return $out;
}

function getTOTP($secret) {
    $time_slice = floor(time() / 30);
    $secret_key = base32_decode($secret);
    if (empty($secret_key)) return false;
    $time = pack('N*', 0) . pack('N*', $time_slice);
    $hmac = hash_hmac('sha1', $time, $secret_key, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hash_part = substr($hmac, $offset, 4);
    $value = unpack('N', $hash_part);
    $value = $value[1] & 0x7FFFFFFF;
    return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
}

function getTOTPWithOffset($secret, $offset_seconds = 0) {
    $time_slice = floor((time() + $offset_seconds) / 30);
    $secret_key = base32_decode($secret);
    if (empty($secret_key)) return false;
    $time = pack('N*', 0) . pack('N*', $time_slice);
    $hmac = hash_hmac('sha1', $time, $secret_key, true);
    $offset = ord(substr($hmac, -1)) & 0x0F;
    $hash_part = substr($hmac, $offset, 4);
    $value = unpack('N', $hash_part);
    $value = $value[1] & 0x7FFFFFFF;
    return str_pad($value % 1000000, 6, '0', STR_PAD_LEFT);
}

function removeCSVRowsBulk($accounts, $filename) {
    $file_path = "data/$filename";
    if (!file_exists($file_path) || empty($accounts)) return;
    $lookup = [];
    foreach ($accounts as $acc) {
        if ($filename == 'gmail_master.csv') { $key = trim($acc['email']); }
        elseif ($filename == 'facebook_cookies_master.csv') { $key = trim($acc['uid']); }
        elseif ($filename == 'instagram_cookies_master.csv') { $key = trim($acc['username']); }
        else { $key = trim($acc['id']) . "|" . trim(str_replace(' ', '', $acc['two_fa'])); }
        $lookup[$key] = true;
    }
    $rows = [];
    if (($handle = fopen($file_path, "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        $rows[] = $headers;
        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($filename == 'gmail_master.csv') { $row_key = trim($data[1]); }
            elseif ($filename == 'facebook_cookies_master.csv') { $row_key = trim($data[0]); }
            elseif ($filename == 'instagram_cookies_master.csv') { $row_key = trim($data[0]); }
            else { $row_key = trim($data[0]) . "|" . trim(str_replace(' ', '', $data[2])); }
            if (isset($lookup[$row_key])) { continue; }
            $rows[] = $data;
        }
        fclose($handle);
    }
    $handle = fopen($file_path, "w");
    foreach ($rows as $row) { fputcsv($handle, $row); }
    fclose($handle);
}

function makeBtn($text, $color = 'primary') {
    $btn = ['text' => $text];
    if (in_array($color, ['red', 'danger', 'লাল'])) $btn['style'] = 'danger';
    elseif (in_array($color, ['green', 'success', 'সবুজ'])) $btn['style'] = 'success';
    elseif (in_array($color, ['blue', 'primary', 'নীল'])) $btn['style'] = 'primary';
    return $btn;
}

function getMainMenu($uid) {
    $menu = [
        [makeBtn('কাজ ▸', 'success'), makeBtn('ব্যালেন্স', 'primary')],
        [makeBtn('টাকা উত্তোলন', 'primary'), makeBtn('My Referrals', 'success')],
        [makeBtn('আমি নতুন', 'success'), makeBtn('সাপোর্ট', 'primary')],
        [makeBtn('Language / ভাষা', 'primary')]
    ];
    if (strval($uid) === strval(ADMIN_ID)) {
        $menu[] = [makeBtn('Admin Dashboard', 'danger')];
    }
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'সেন্ট্রাল শিট', 'Central Sheet'), 'success'), makeBtn(L($user_lang, 'পেন্ডিং উইথড্র', 'Pending Withdraw'), 'primary')],
        [makeBtn(L($user_lang, 'পরিসংখ্যান', 'Statistics'), 'primary'), makeBtn(L($user_lang, 'ইউজার সার্চ', 'User Search'), 'primary')],
        [makeBtn(L($user_lang, 'ব্রডকাস্ট', 'Broadcast'), 'primary'), makeBtn(L($user_lang, 'বটের সেটিংস', 'Bot Settings'), 'primary')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminCentralSheetMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ইনস্টাগ্রাম কন্ট্রোল', 'Instagram Control'), 'primary'), makeBtn(L($user_lang, 'জিমেইল কন্ট্রোল', 'Gmail Control'), 'primary')],
        [makeBtn(L($user_lang, 'ফেসবুক কন্ট্রোল', 'Facebook Control'), 'primary'), makeBtn(L($user_lang, 'কুকিজ কন্ট্রোল', 'Cookies Control'), 'primary')], 
        [makeBtn(L($user_lang, 'ইনস্টা কুকিজ কন্ট্রোল', 'Insta Cookies Control'), 'primary'), makeBtn(L($user_lang, '🆕 কাস্টম কাজ শিট', '🆕 Custom Task Sheets'), 'success')],
        [makeBtn(L($user_lang, 'ডাটাবেজ ক্লিনআপ', 'Database Cleanup'), 'danger')],
        [makeBtn(L($user_lang, '🔙 এডমিন মেনু', '🔙 Admin Menu'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getCustomTaskSheetsMenu() {
    global $user_lang;
    $tasks=readTasks(); $menu=[];
    foreach ($tasks as $t) {
        $emoji=$t['emoji']??'🆕'; $name=$t['name']??$t['id'];
        // Download is read-only: it NEVER removes rows/seeds.
        $menu[]=[makeBtn("{$emoji} {$name} — 📥 CSV",'primary')];
        $menu[]=[
            makeBtn("{$emoji} {$name} — ✅ ভালো আইডি",'success'),
            makeBtn("{$emoji} {$name} — ❌ খারাপ আইডি",'danger')
        ];
    }
    if (empty($menu)) $menu[]=[makeBtn(L($user_lang,'❌ কোনো কাস্টম কাজ নেই','❌ No custom tasks'),'danger')];
    $menu[]=[makeBtn(L($user_lang,'🔙 সেন্ট্রাল শিট','🔙 Central Sheet'),'primary')];
    return json_encode(['keyboard'=>$menu,'resize_keyboard'=>true]);
}

function removeDynamicTaskRowsByValues($task_id, $values, $delimiter = ',') {
    $path = taskCsvPath($task_id);
    if (!file_exists($path) || empty($values)) return 0;
    $values = array_values(array_filter(array_map(function($v){ return strtolower(trim((string)$v)); }, $values)));
    if (empty($values)) return 0;
    $rows=[]; $removed=0;
    $fh=fopen($path,'r');
    if (!$fh) return 0;
    $header=fgetcsv($fh);
    if ($header !== false) $rows[]=$header;
    while (($row=fgetcsv($fh)) !== false) {
        $matched=false;
        foreach ($row as $cell) {
            $celln=strtolower(trim((string)$cell));
            if ($celln === '') continue;
            foreach ($values as $v) {
                if ($v !== '' && ($celln === $v || strpos($celln,$v)!==false)) { $matched=true; break 2; }
            }
        }
        if ($matched) $removed++; else $rows[]=$row;
    }
    fclose($fh);
    $out=fopen($path,'w');
    if (!$out) return 0;
    foreach ($rows as $r) fputcsv($out,$r);
    fclose($out);
    return $removed;
}

function processDynamicTaskReport($task_id, $approved, $lines, $delimiter, $chat_id, $admin_id, $lang) {
    $tasks=readTasks(); $task=findTaskById($tasks,$task_id);
    if ($task===null) return false;
    $users=readDB('users.json');
    $proofs=readDB('proofs.json');
    $processed=0; $removed_values=[];
    foreach ($lines as $line) {
        if (empty(trim($line))) continue;
        $report_row=str_getcsv($line,$delimiter);
        if (empty($report_row)) continue;
        foreach ($proofs as $k=>$proof) {
            if (($proof['status']??'')!=='pending' || ($proof['task_type']??'')!=='dynamic' || ($proof['task_id']??'')!==$task_id) continue;
            $data=$proof['data']??[];
            $matched=false; $match_value='';
            foreach ($data as $v) {
                if (is_array($v)) continue;
                $v=trim((string)$v);
                if ($v==='') continue;
                if (rowContainsValue($report_row,$v)) { $matched=true; $match_value=$v; break; }
            }
            if (!$matched) continue;

            $uid=$proof['user_id'];
            if ($approved) {
                $proofs[$k]['status']='approved';
                $reward=floatval($task['reward']??0);
                if (isset($users[$uid])) {
                    $users[$uid]['balance']=floatval($users[$uid]['balance']??0)+$reward;
                    $users[$uid]['total_income']=floatval($users[$uid]['total_income']??0)+$reward;
                    $users[$uid]['completed_tasks']=intval($users[$uid]['completed_tasks']??0)+1;
                    $users[$uid]['review_tasks']=max(0,intval($users[$uid]['review_tasks']??0)-1);
                    $referrer=$users[$uid]['ref_by']??0;
                    if (strval($referrer)!=='0' && isset($users[$referrer])) {
                        $comm_pct=floatval($s['ref_commission']??5);
                        $comm=$reward*($comm_pct/100);
                        $users[$referrer]['balance']=floatval($users[$referrer]['balance']??0)+$comm;
                        $users[$referrer]['ref_income']=floatval($users[$referrer]['ref_income']??0)+$comm;
                        bot('sendMessage',['chat_id'=>$referrer,'text'=>pe('👥','5460795800101594035')." <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>",'parse_mode'=>'HTML']);
                    }
                    $identity = '';
                    if (!empty($data['username'])) $identity .= "\n👤 Username: <code>".htmlspecialchars((string)$data['username'])."</code>";
                    if (!empty($data['uid'])) $identity .= "\n🆔 UID: <code>".htmlspecialchars((string)$data['uid'])."</code>";
                    if ($identity === '') $identity = "\n👤 Username/UID: <code>—</code>";
                    bot('sendMessage',['chat_id'=>$uid,'text'=>pe('✅','5206607081334906820')." <b>কাজ এপ্রুভ!</b>\n📌 কাজ: <b>".htmlspecialchars($task['name'])."</b>".$identity."\n💰 রিওয়ার্ড: ৳{$reward}",'parse_mode'=>'HTML']);
                }
            } else {
                $proofs[$k]['status']='rejected';
                if (isset($users[$uid])) {
                    $users[$uid]['review_tasks']=max(0,intval($users[$uid]['review_tasks']??0)-1);
                    $users[$uid]['rejected_tasks']=intval($users[$uid]['rejected_tasks']??0)+1;
                    $identity = '';
                    if (!empty($data['username'])) $identity .= "\n👤 Username: <code>".htmlspecialchars((string)$data['username'])."</code>";
                    if (!empty($data['uid'])) $identity .= "\n🆔 UID: <code>".htmlspecialchars((string)$data['uid'])."</code>";
                    if ($identity === '') $identity = "\n👤 Username/UID: <code>—</code>";
                    bot('sendMessage',['chat_id'=>$uid,'text'=>pe('❌','5210952531676504517')." <b>কাজ রিজেক্ট!</b>\n📌 কাজ: <b>".htmlspecialchars($task['name'])."</b>".$identity."\n📝 কারণ: রিপোর্ট/আইডি সঠিক নয়।",'parse_mode'=>'HTML']);
                }
            }
            $removed_values[]=$match_value;
            $processed++;
            break;
        }
    }
    writeDB('proofs.json',$proofs);
    writeDB('users.json',$users);
    if (!empty($removed_values)) removeDynamicTaskRowsByValues($task_id,$removed_values,$delimiter);
    bot('sendMessage',['chat_id'=>$chat_id,'text'=>($approved?pe('✅','5206607081334906820'):pe('❌','5210952531676504517'))." <b>{$task['name']}</b> রিপোর্ট প্রসেস সম্পন্ন। মোট: <b>{$processed}</b>",'parse_mode'=>'HTML','reply_markup'=>getCustomTaskSheetsMenu()]);
    setState($admin_id,'');
    return true;
}

function getInstaControlMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ইনস্টাগ্রাম শিট ডাউনলোড', 'Download Instagram Sheet'), 'primary')],
        [makeBtn(L($user_lang, 'ইনস্টা ভালো আইডি', 'Insta Good ID'), 'success'), makeBtn(L($user_lang, 'ইনস্টা খারাপ আইডি', 'Insta Bad ID'), 'danger')],
        [makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'primary')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getCookiesControlMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'কুকিজ শিট ডাউনলোড', 'Download Cookies Sheet'), 'primary')],
        [makeBtn(L($user_lang, 'কুকিজ ভালো আইডি', 'Cookies Good ID'), 'success'), makeBtn(L($user_lang, 'কুকিজ খারাপ আইডি', 'Cookies Bad ID'), 'danger')],
        [makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'primary')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getInstaCookiesControlMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ইনস্টা কুকিজ শিট ডাউনলোড', 'Download Insta Cookies Sheet'), 'primary')],
        [makeBtn(L($user_lang, 'ইনস্টা কুকিজ ভালো আইডি', 'Insta Cookies Good ID'), 'success'), makeBtn(L($user_lang, 'ইনস্টা কুকিজ খারাপ আইডি', 'Insta Cookies Bad ID'), 'danger')],
        [makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'primary')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getGmailControlMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'জিমেইল শিট ডাউনলোড', 'Download Gmail Sheet'), 'primary')],
        [makeBtn(L($user_lang, 'জিমেইল ভালো আইডি', 'Gmail Good ID'), 'success'), makeBtn(L($user_lang, 'জিমেইল খারাপ আইডি', 'Gmail Bad ID'), 'danger')],
        [makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'primary')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getFacebookControlMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ফেসবুক শিট ডাউনলোড', 'Download Facebook Sheet'), 'primary')],
        [makeBtn(L($user_lang, 'ফেসবুক ভালো আইডি', 'Facebook Good ID'), 'success'), makeBtn(L($user_lang, 'ফেসবুক খারাপ আইডি', 'Facebook Bad ID'), 'danger')],
        [makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'primary')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminSettingsMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, '🆕 কাজ ম্যানেজমেন্ট', '🆕 Task Management'), 'success')],
        [makeBtn('কাজের মূল্য সেট', 'primary'), makeBtn('পাসওয়ার্ড সেটিংস', 'primary')],
        [makeBtn('🎥 কাজের ভিডিও সেট', 'primary'), makeBtn('রেফার কমিশন সেট', 'primary')],
        [makeBtn('লিমিট ও চার্জ সেট', 'primary'), makeBtn('চ্যানেল সেটিংস', 'primary')],
        [makeBtn(L($user_lang, 'কাজ চালু/বন্ধ', 'Task On/Off'), 'success'), makeBtn(L($user_lang, 'পেমেন্ট অন/অফ', 'Payment On/Off'), 'success')],
        [makeBtn('উইথড্র মেথড সেটিংস', 'success')],
        [makeBtn('📊 সিড কন্ট্রোল', 'primary'), makeBtn('💾 ব্যাকআপ', 'danger')],
        [makeBtn(L($user_lang, '🔙 এডমিন মেনু', '🔙 Admin Menu'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminPaymentToggleMenu($s) {
    global $user_lang;
    $bkash_status = ($s['status_wd_bkash'] ?? 'open') == 'open' ? L($user_lang, '🔴 বিকাশ উইথড্র বন্ধ', '🔴 bKash Withdraw Off') : L($user_lang, '🟢 বিকাশ উইথড্র চালু', '🟢 bKash Withdraw On');
    $nagad_status = ($s['status_wd_nagad'] ?? 'open') == 'open' ? L($user_lang, '🔴 নগদ উইথড্র বন্ধ', '🔴 Nagad Withdraw Off') : L($user_lang, '🟢 নগদ উইথড্র চালু', '🟢 Nagad Withdraw On');
    $rocket_status = ($s['status_wd_rocket'] ?? 'open') == 'open' ? L($user_lang, '🔴 রকেট উইথড্র বন্ধ', '🔴 Rocket Withdraw Off') : L($user_lang, '🟢 রকেট উইথড্র চালু', '🟢 Rocket Withdraw On');
    $menu = [
        [makeBtn($bkash_status, 'primary'), makeBtn($nagad_status, 'primary')],
        [makeBtn($rocket_status, 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminTaskToggleMenu($s) {
    global $user_lang;
    $insta_status = ($s['status_instagram'] ?? 'open') == 'open' ? L($user_lang, '🔴 ইনস্টা বন্ধ করুন', '🔴 Turn Off Instagram') : L($user_lang, '🟢 ইনস্টা চালু করুন', '🟢 Turn On Instagram');
    $gmail_status = ($s['status_gmail'] ?? 'open') == 'open' ? L($user_lang, '🔴 জিমেইল বন্ধ করুন', '🔴 Turn Off Gmail') : L($user_lang, '🟢 জিমেইল চালু করুন', '🟢 Turn On Gmail');
    $fb_status = ($s['status_facebook'] ?? 'open') == 'open' ? L($user_lang, '🔴 ফেসবুক বন্ধ করুন', '🔴 Turn Off Facebook') : L($user_lang, '🟢 ফেসবুক চালু করুন', '🟢 Turn On Facebook');
    $cookies_status = ($s['status_facebook_cookies'] ?? 'open') == 'open' ? L($user_lang, '🔴 কুকিজ বন্ধ করুন', '🔴 Turn Off Cookies') : L($user_lang, '🟢 কুকিজ চালু করুন', '🟢 Turn On Cookies');
    $insta_cookies_status = ($s['status_instagram_cookies'] ?? 'open') == 'open' ? L($user_lang, '🔴 ইনস্টা কুকিজ বন্ধ করুন', '🔴 Turn Off Insta Cookies') : L($user_lang, '🟢 ইনস্টা কুকিজ চালু করুন', '🟢 Turn On Insta Cookies');
    $menu = [
        [makeBtn($insta_status, 'primary'), makeBtn($gmail_status, 'primary')],
        [makeBtn($fb_status, 'primary'), makeBtn($cookies_status, 'primary')],
        [makeBtn($insta_cookies_status, 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminPasswordSettingsMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ইনস্টা পাসওয়ার্ড', 'Insta Password'), 'primary'), makeBtn(L($user_lang, 'জিমেইল পাসওয়ার্ড', 'Gmail Password'), 'primary')],
        [makeBtn(L($user_lang, 'ফেসবুক পাসওয়ার্ড', 'Facebook Password'), 'primary'), makeBtn(L($user_lang, 'কুকিজ পাসওয়ার্ড', 'Cookies Password'), 'primary')],
        [makeBtn(L($user_lang, 'ইনস্টা কুকিজ পাসওয়ার্ড', 'Insta Cookies Password'), 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminRewardSettingsMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ইনস্টা কাজের মূল্য', 'Insta Task Price'), 'primary'), makeBtn(L($user_lang, 'জিমেইল কাজের মূল্য', 'Gmail Task Price'), 'primary')],
        [makeBtn(L($user_lang, 'ফেসবুক কাজের মূল্য', 'Facebook Task Price'), 'primary'), makeBtn(L($user_lang, 'কুকিজ কাজের মূল্য', 'Cookies Task Price'), 'primary')],
        [makeBtn(L($user_lang, 'ইনস্টা কুকিজ কাজের মূল্য', 'Insta Cookies Task Price'), 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminLimitSettingsMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'বিকাশ লিমিট', 'bKash Limit'), 'primary'), makeBtn(L($user_lang, 'বিকাশ চার্জ', 'bKash Charge'), 'primary')],
        [makeBtn(L($user_lang, 'নগদ লিমিট', 'Nagad Limit'), 'primary'), makeBtn(L($user_lang, 'নগদ চার্জ', 'Nagad Charge'), 'primary')],
        [makeBtn(L($user_lang, 'রকেট লিমিট', 'Rocket Limit'), 'primary'), makeBtn(L($user_lang, 'রকেট চার্জ', 'Rocket Charge'), 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function getAdminChannelSettingsMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'ফোর্স ইউজারনেম', 'Force Username'), 'primary'), makeBtn(L($user_lang, 'অফিশিয়াল লিংক', 'Official Link'), 'primary')],
        [makeBtn(L($user_lang, '📞 সাপোর্ট লিংক সেট', '📞 Set Support Link'), 'primary')],
        [makeBtn(L($user_lang, 'জয়েন চ্যানেল ম্যানেজমেন্ট', 'Join Channel Management'), 'success')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function showAdminWithdrawList($chat_id) {
    global $user_lang;
    $withdraws = readDB('withdraws.json');
    $inline_kb = [];
    foreach ($withdraws as $w) {
        if ($w['status'] == 'pending') {
            $method_upper = strtoupper($w['method']);
            $inline_kb[] = [
                ['text' => "📱 [{$method_upper}] ৳{$w['amount']} - User: {$w['user_id']}", 'callback_data' => "view_wd_{$w['id']}"]
            ];
        }
    }
    if (empty($inline_kb)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " " . L($user_lang, "বর্তমানে কোনো পেন্ডিং উইথড্রাল রিকোয়েস্ট নেই।", "There are currently no pending withdrawal requests."), 'parse_mode' => 'HTML']);
    } else {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📥', '5406745015365943482') . " " . L($user_lang,
                "<b>পেন্ডিং উইথড্রাল তালিকা:</b>\n\nযেকোনো উইথড্রর বিস্তারিত দেখতে এবং অ্যাকশন নিতে সেটির উপর ক্লিক করুন:",
                "<b>Pending Withdrawal List:</b>\n\nTap any withdrawal to view its details and take action:"
            ),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
        ]);
    }
}

function readMethods() { return readDB('withdraw_methods.json'); }
function writeMethodsDB($data) { return writeDB('withdraw_methods.json', $data); }

function genMethodId($name) {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    $slug = trim($slug, '_');
    if (empty($slug)) $slug = 'method';
    return $slug . '_' . substr(uniqid(), -6);
}

function findMethodIndex($methods, $id) {
    foreach ($methods as $idx => $m) {
        if ($m['id'] === $id) return $idx;
    }
    return -1;
}

function findMethodById($methods, $id) {
    foreach ($methods as $m) {
        if ($m['id'] === $id) return $m;
    }
    return null;
}

function methodButtonLabel($m, $lang = 'bn') {
    $min = $m['min'];
    $charge = $m['charge'];
    $min_word = L($lang, 'সর্বনিম্ন', 'Min');
    return "🔸 {$m['name']} -> {$min_word}: {$min}৳(-{$charge})";
}

function getMethodManageMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'নতুন মেথড যোগ করুন', 'Add New Method'), 'success'), makeBtn(L($user_lang, '📋 সব মেথড দেখুন', '📋 View All Methods'), 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function showAdminMethodList($chat_id) {
    global $user_lang;
    $methods = readMethods();
    if (empty($methods)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🧩', '5406745015365943482') . " " . L($user_lang, "এখনো কোনো কাস্টম উইথড্রাল মেথড যোগ করা হয়নি। ➕ বাটনে চেপে নতুন মেথড যোগ করুন।", "No custom withdrawal method has been added yet. Tap the ➕ button to add a new method."),
            'parse_mode' => 'HTML',
            'reply_markup' => getMethodManageMenu()
        ]);
        return;
    }
    $min_word = L($user_lang, 'মিনিমাম', 'Min');
    $charge_word = L($user_lang, 'চার্জ', 'Charge');
    $inline_kb = [];
    foreach ($methods as $m) {
        $status_icon = (($m['status'] ?? 'open') == 'open') ? '🟢' : '🔴';
        $inline_kb[] = [
            ['text' => "{$status_icon} {$m['name']} ({$min_word}: {$m['min']}৳, {$charge_word}: {$m['charge']}৳)", 'callback_data' => "mm_view_{$m['id']}"]
        ];
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🧩', '5406745015365943482') . " " . L($user_lang,
            "<b>কাস্টম উইথড্রাল মেথড তালিকা:</b>\n\nবিস্তারিত দেখতে, এডিট, ডিলিট অথবা চালু/বন্ধ করতে একটি মেথডে ক্লিক করুন:",
            "<b>Custom Withdrawal Method List:</b>\n\nTap a method to view details, edit, delete, or enable/disable it:"
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
    ]);
}

function methodDetailView($m) {
    global $user_lang;
    $status_text = (($m['status'] ?? 'open') == 'open') ? L($user_lang, '🟢 চালু', '🟢 On') : L($user_lang, '🔴 বন্ধ', '🔴 Off');
    $toggle_label = (($m['status'] ?? 'open') == 'open') ? L($user_lang, '🔴 বন্ধ করুন', '🔴 Turn Off') : L($user_lang, '🟢 চালু করুন', '🟢 Turn On');
    $inline_kb = [
        [['text' => $toggle_label, 'callback_data' => "mm_toggle_{$m['id']}"]],
        [
            ['text' => L($user_lang, '✏️ নাম এডিট', '✏️ Edit Name'), 'callback_data' => "mm_editfield_name_{$m['id']}"],
            ['text' => L($user_lang, '✏️ ফিল্ড লেবেল এডিট', '✏️ Edit Field Label'), 'callback_data' => "mm_editfield_field_label_{$m['id']}"]
        ],
        [
            ['text' => L($user_lang, '✏️ মিনিমাম এডিট', '✏️ Edit Minimum'), 'callback_data' => "mm_editfield_min_{$m['id']}"],
            ['text' => L($user_lang, '✏️ চার্জ এডিট', '✏️ Edit Charge'), 'callback_data' => "mm_editfield_charge_{$m['id']}"]
        ],
        [['text' => L($user_lang, '🗑️ ডিলিট করুন', '🗑️ Delete'), 'callback_data' => "mm_del_{$m['id']}"]],
        [['text' => L($user_lang, '🔙 তালিকায় ফিরে যান', '🔙 Back to List'), 'callback_data' => 'list_mm']]
    ];
    $detail_word = L($user_lang, 'মেথড বিস্তারিত', 'Method Details');
    $name_word = L($user_lang, 'নাম', 'Name');
    $field_word = L($user_lang, 'Account/ID ফিল্ড', 'Account/ID Field');
    $min_word = L($user_lang, 'সর্বনিম্ন', 'Minimum');
    $charge_word = L($user_lang, 'চার্জ', 'Charge');
    $status_word = L($user_lang, 'স্ট্যাটাস', 'Status');
    $text = pe('🧩', '5406745015365943482') . " <b>{$detail_word}:</b>\n\n🏷️ {$name_word}: <b>{$m['name']}</b>\n📝 {$field_word}: {$m['field_label']}\n💰 {$min_word}: {$m['min']}৳\n💸 {$charge_word}: {$m['charge']}৳\n📊 {$status_word}: {$status_text}";
    return ['text' => $text, 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])];
}

function genChannelId($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    $slug = trim($slug, '_');
    if (empty($slug)) $slug = 'ch';
    return $slug . '_' . substr(uniqid(), -6);
}

function findChannelIndex($channels, $id) {
    foreach ($channels as $idx => $c) {
        if ($c['id'] === $id) return $idx;
    }
    return -1;
}

function findChannelById($channels, $id) {
    foreach ($channels as $c) {
        if ($c['id'] === $id) return $c;
    }
    return null;
}

function colorPickerKeyboard($callback_prefix, $suffix = '') {
    $colors = ['red' => '🔴', 'green' => '🟢', 'blue' => '🔵', 'yellow' => '🟡', 'purple' => '🟣', 'orange' => '🟠'];
    $row1 = [];
    $row2 = [];
    $i = 0;
    foreach ($colors as $code => $emoji) {
        $btn = ['text' => $emoji, 'callback_data' => "{$callback_prefix}_{$code}{$suffix}"];
        if ($i < 3) { $row1[] = $btn; } else { $row2[] = $btn; }
        $i++;
    }
    return [$row1, $row2];
}

function colorCodeToEmoji($code) {
    $colors = ['red' => '🔴', 'green' => '🟢', 'blue' => '🔵', 'yellow' => '🟡', 'purple' => '🟣', 'orange' => '🟠'];
    return $colors[$code] ?? '📢';
}

function getJoinChannelManageMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, 'নতুন চ্যানেল যোগ করুন', 'Add New Channel'), 'success'), makeBtn(L($user_lang, '📋 সব চ্যানেল দেখুন', '📋 View All Channels'), 'primary')],
        [makeBtn(L($user_lang, '🔙 চ্যানেল সেটিংস', '🔙 Channel Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function showAdminChannelList($chat_id, $s) {
    $channels = $s['join_channels'] ?? [];
    global $user_lang;
    if (empty($channels)) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📢', '5424818078833715060') . " " . L($user_lang, "এখনো কোনো জয়েন চ্যানেল যোগ করা হয়নি। ➕ বাটনে চেপে নতুন চ্যানেল যোগ করুন।", "No join channel has been added yet. Tap the ➕ button to add a new channel."),
            'parse_mode' => 'HTML',
            'reply_markup' => getJoinChannelManageMenu()
        ]);
        return;
    }
    $inline_kb = [];
    foreach ($channels as $c) {
        $status_icon = (($c['status'] ?? 'open') == 'open') ? '🟢' : '🔴';
        $color = $c['color'] ?? '📢';
        $inline_kb[] = [
            ['text' => "{$status_icon} {$color} {$c['title']}", 'callback_data' => "jc_view_{$c['id']}"]
        ];
    }
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('📢', '5424818078833715060') . " " . L($user_lang,
            "<b>জয়েন চ্যানেল তালিকা:</b>\n\nবিস্তারিত দেখতে, এডিট, ডিলিট, রঙ পরিবর্তন অথবা চালু/বন্ধ করতে একটি চ্যানেলে ক্লিক করুন:",
            "<b>Join Channel List:</b>\n\nTap a channel to view details, edit, delete, change color, or enable/disable it:"
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
    ]);
}

function channelDetailView($c) {
    global $user_lang;
    $status_text = (($c['status'] ?? 'open') == 'open') ? L($user_lang, '🟢 চালু', '🟢 On') : L($user_lang, '🔴 বন্ধ', '🔴 Off');
    $toggle_label = (($c['status'] ?? 'open') == 'open') ? L($user_lang, '🔴 বন্ধ করুন', '🔴 Turn Off') : L($user_lang, '🟢 চালু করুন', '🟢 Turn On');
    $color = $c['color'] ?? '📢';
    $inline_kb = [
        [['text' => $toggle_label, 'callback_data' => "jc_toggle_{$c['id']}"]],
        [
            ['text' => L($user_lang, '✏️ টাইটেল এডিট', '✏️ Edit Title'), 'callback_data' => "jc_editfield_title_{$c['id']}"],
            ['text' => L($user_lang, '✏️ আইডি এডিট', '✏️ Edit ID'), 'callback_data' => "jc_editfield_chatid_{$c['id']}"]
        ],
        [
            ['text' => L($user_lang, '✏️ লিংক এডিট', '✏️ Edit Link'), 'callback_data' => "jc_editfield_url_{$c['id']}"],
            ['text' => L($user_lang, '🎨 রঙ পরিবর্তন', '🎨 Change Color'), 'callback_data' => "jc_colormenu_{$c['id']}"]
        ],
        [['text' => L($user_lang, '🗑️ ডিলিট করুন', '🗑️ Delete'), 'callback_data' => "jc_del_{$c['id']}"]],
        [['text' => L($user_lang, '🔙 তালিকায় ফিরে যান', '🔙 Back to List'), 'callback_data' => 'list_jc']]
    ];
    $title_word = L($user_lang, 'টাইটেল', 'Title');
    $id_word = L($user_lang, 'চ্যানেল আইডি', 'Channel ID');
    $link_word = L($user_lang, 'জয়েন লিংক', 'Join Link');
    $status_word = L($user_lang, 'স্ট্যাটাস', 'Status');
    $detail_word = L($user_lang, 'চ্যানেল বিস্তারিত', 'Channel Details');
    $text = pe('📢', '5424818078833715060') . " <b>{$detail_word}:</b>\n\n{$color} {$title_word}: <b>{$c['title']}</b>\n🆔 {$id_word}: <code>{$c['chat_id']}</code>\n🔗 {$link_word}: {$c['url']}\n📊 {$status_word}: {$status_text}";
    return ['text' => $text, 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])];
}

// ============================================================
// 🆕 ডাইনামিক টাস্ক ম্যানেজমেন্ট ফাংশন
// ============================================================

function readTasks() {
    $data = readDB('tasks.json');
    return is_array($data) ? $data : [];
}

function writeTasks($data) {
    return writeDB('tasks.json', $data);
}

function genTaskId($name) {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
    $slug = trim($slug, '_');
    if (empty($slug)) $slug = 'task';
    return $slug . '_' . substr(uniqid(), -6);
}

function findTaskIndex($tasks, $id) {
    foreach ($tasks as $idx => $t) {
        if ($t['id'] === $id) return $idx;
    }
    return -1;
}

function findTaskById($tasks, $id) {
    foreach ($tasks as $t) {
        if ($t['id'] === $id) return $t;
    }
    return null;
}

function taskCsvPath($task_id) {
    return "data/tasks_csv/{$task_id}_master.csv";
}

function getTaskVideo($task) {
    global $s;
    if (!is_array($task)) return '';
    if (!empty($task['video'])) return trim((string)$task['video']);
    $id = $task['id'] ?? '';
    return ($id !== '' && !empty($s['task_video_'.$id])) ? trim((string)$s['task_video_'.$id]) : '';
}

function setTaskVideo($task, $video) {
    global $s;
    $id = $task['id'] ?? '';
    if ($id === '') return false;
    $video = trim((string)$video);
    if (($task['_source'] ?? 'custom') === 'legacy') {
        if ($video === '') unset($s['task_video_'.$id]);
        else $s['task_video_'.$id] = $video;
        return writeDB('settings.json', $s);
    }
    $tasks = readTasks(); $idx = findTaskIndex($tasks, $id);
    if ($idx === -1) return false;
    $tasks[$idx]['video'] = $video;
    return writeTasks($tasks);
}

function taskVideoButton($video, $lang='bn') {
    if (empty($video)) return [];
    return [[['text'=>L($lang,'🎥 কাজের ভিডিও দেখুন','🎥 Watch Task Video'),'url'=>$video]]];
}

function taskFieldButtonDefault($field, $lang='bn') {
    $map = [
        'uid' => ['bn'=>'🆔 UID সাবমিট করুন','en'=>'🆔 Submit UID'],
        '2fa' => ['bn'=>'🔐 2FA সাবমিট করুন','en'=>'🔐 Submit 2FA'],
        'cookies' => ['bn'=>'🍪 Cookies সাবমিট করুন','en'=>'🍪 Submit Cookies']
    ];
    return $map[$field][$lang] ?? ($map[$field]['bn'] ?? ('📝 '.ucfirst($field)));
}

function taskFieldButtonLabel($task, $field, $lang='bn') {
    $labels = $task['field_button_labels'] ?? [];
    if (isset($labels[$field]) && trim((string)$labels[$field]) !== '') return (string)$labels[$field];
    return taskFieldButtonDefault($field, $lang);
}

function taskInputButtonKeyboard($task, $remaining, $lang='bn', $includeSubmit=false) {
    // গুরুত্বপূর্ণ: UID / Cookies / 2FA একসাথে দেখানো হবে না।
    // input_fields যেই ক্রমে Admin যোগ করেছে, সেই ক্রমে শুধু প্রথম অসম্পূর্ণ
    // field-এর একটি বাটন দেখানো হবে। সেটি সফলভাবে জমা হলে পরের field-এর
    // বাটন দেখাবে।
    $rows = [];
    $next_field = '';
    foreach ($remaining as $field) {
        if (in_array($field, ['uid','2fa','cookies'], true)) {
            $next_field = $field;
            break;
        }
    }

    if ($next_field !== '') {
        $rows[] = [makeBtn(taskFieldButtonLabel($task, $next_field, $lang), 'primary')];
    } elseif ($includeSubmit) {
        $rows[] = [makeBtn(L($lang,'✅ কাজ সাবমিট','✅ Submit Task'),'success')];
    }

    $rows[] = [makeBtn(L($lang,'বাতিল','Cancel'),'danger')];
    return json_encode(['keyboard'=>$rows,'resize_keyboard'=>true]);
}

function taskFieldLabels() {
    return [
        'username' => ['bn' => 'ইউজারনেম (অটো)', 'en' => 'Username (auto)', 'emoji' => '👤'],
        'email'    => ['bn' => 'ইমেইল (অটো)', 'en' => 'Email (auto)', 'emoji' => '📧'],
        'fb_name'  => ['bn' => 'প্রথম+শেষ নাম (অটো)', 'en' => 'First+Last Name (auto)', 'emoji' => '👥'],
        'uid'      => ['bn' => 'UID', 'en' => 'UID', 'emoji' => '🆔'],
        'password' => ['bn' => 'পাসওয়ার্ড (এডমিন সেট)', 'en' => 'Password (admin set)', 'emoji' => '🔐'],
        '2fa'      => ['bn' => '2FA Key', 'en' => '2FA Key', 'emoji' => '🔑'],
        'cookies'  => ['bn' => 'Cookies', 'en' => 'Cookies', 'emoji' => '🍪']
    ];
}

function getLegacyTaskDefinitions() {
    global $s;
    return [
        'legacy_instagram' => ['name_key'=>'insta_2fa_label','name_bn'=>'ইনস্টা 2FA','name_en'=>'Insta 2FA','emoji'=>'📸','reward_key'=>'reward_instagram','status_key'=>'status_instagram','password_key'=>'admin_insta_password','csv'=>'data/accounts_master.csv','source'=>'legacy'],
        'legacy_gmail' => ['name_key'=>'gmail_label','name_bn'=>'জিমেইল কাজ','name_en'=>'Gmail Task','emoji'=>'📧','reward_key'=>'reward_gmail','status_key'=>'status_gmail','password_key'=>'admin_gmail_password','csv'=>'data/gmail_master.csv','source'=>'legacy'],
        'legacy_facebook' => ['name_key'=>'fb_2fa_label','name_bn'=>'ফেসবুক 2FA','name_en'=>'Facebook 2FA','emoji'=>'👥','reward_key'=>'reward_facebook','status_key'=>'status_facebook','password_key'=>'admin_fb_password','csv'=>'data/accounts_master.csv','source'=>'legacy'],
        'legacy_facebook_cookies' => ['name_key'=>'fb_cookies_label','name_bn'=>'ফেসবুক কুকিজ','name_en'=>'Facebook Cookies','emoji'=>'🍪','reward_key'=>'reward_facebook_cookies','status_key'=>'status_facebook_cookies','password_key'=>'admin_fb_cookies_password','csv'=>'data/facebook_cookies_master.csv','source'=>'legacy'],
        'legacy_instagram_cookies' => ['name_key'=>'insta_cookies_label','name_bn'=>'ইনস্টা কুকিজ','name_en'=>'Insta Cookies','emoji'=>'🍪','reward_key'=>'reward_instagram_cookies','status_key'=>'status_instagram_cookies','password_key'=>'admin_instagram_cookies_password','csv'=>'data/instagram_cookies_master.csv','source'=>'legacy']
    ];
}

function legacyTaskIsDeleted($id) {
    global $s;
    return !empty($s['deleted_'.$id]);
}

function getManagedTaskById($id) {
    $tasks = readTasks();
    $t = findTaskById($tasks, $id);
    if ($t !== null) { $t['_source'] = 'custom'; return $t; }
    $defs = getLegacyTaskDefinitions();
    if (!isset($defs[$id])) return null;
    global $s;
    $d = $defs[$id];
    return [
        'id'=>$id, 'name'=>($s['custom_name_'.$id] ?? $d['name_bn']), 'name_en'=>$d['name_en'], 'emoji'=>$d['emoji'],
        'reward'=>floatval($s[$d['reward_key']] ?? 0), 'password'=>$s[$d['password_key']] ?? '',
        'status'=>($s[$d['status_key']] ?? 'open'), 'input_fields'=>[],
        'uid_min_length'=>10, 'uid_max_length'=>16, 'instruction'=>'', 'sheet_url'=>'', 'sheet_name'=>$d['name_en'],
        '_source'=>'legacy', '_def'=>$d
    ];
}

function getLegacyTaskCsv($id) {
    $defs = getLegacyTaskDefinitions();
    return isset($defs[$id]) ? $defs[$id]['csv'] : '';
}

function getTaskManageMenu() {
    global $user_lang;
    $menu = [
        [makeBtn(L($user_lang, '➕ নতুন কাজ যোগ করুন', '➕ Add New Task'), 'success'), makeBtn(L($user_lang, '📋 সব কাজ দেখুন', '📋 View All Tasks'), 'primary')],
        [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]
    ];
    return json_encode(['keyboard' => $menu, 'resize_keyboard' => true]);
}

function showAdminTaskList($chat_id) {
    global $user_lang;
    $tasks = readTasks();
    $managed = [];
    foreach ($tasks as $t) { $t['_source'] = 'custom'; $managed[] = $t; }
    foreach (getLegacyTaskDefinitions() as $id => $def) {
        if (legacyTaskIsDeleted($id)) continue;
        $t = getManagedTaskById($id);
        if ($t !== null) $managed[] = $t;
    }
    if (empty($managed)) {
        bot('sendMessage', [
            'chat_id'=>$chat_id,
            'text'=>pe('🆕','5397916757333654639')." ".L($user_lang,"এখনো কোনো কাজ নেই। ➕ বাটনে চেপে নতুন কাজ যোগ করুন।","No tasks found. Tap ➕ to add a new task."),
            'parse_mode'=>'HTML','reply_markup'=>getTaskManageMenu()
        ]); return;
    }
    $inline_kb=[];
    foreach ($managed as $t) {
        $status_icon=(($t['status']??'open')==='open')?'🟢':'🔴';
        $emoji=$t['emoji']??'🆕'; $reward=number_format($t['reward']??0,2);
        $src=(($t['_source']??'custom')==='legacy')?'old':'new';
        $inline_kb[]=[['text'=>"{$status_icon} {$emoji} {$t['name']} (৳{$reward})",'callback_data'=>"tsk_view_{$src}_{$t['id']}"]];
    }
    bot('sendMessage', [
        'chat_id'=>$chat_id,
        'text'=>pe('🆕','5397916757333654639')." ".L($user_lang,"<b>সব কাজের তালিকা:</b>\n\nএখান থেকে পুরোনো ৫টি ও নতুন যোগ করা সব কাজের Edit, Delete এবং On/Off করা যাবে।","<b>All Tasks:</b>\n\nYou can Edit, Delete and On/Off both the old 5 tasks and all new tasks here."),
        'parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>$inline_kb])
    ]);
}

function legacyTaskDetailView($t,$lang='bn') {
    $status=(($t['status']??'open')==='open')?L($lang,'🟢 চালু','🟢 On'):L($lang,'🔴 বন্ধ','🔴 Off');
    $toggle=(($t['status']??'open')==='open')?L($lang,'🔴 বন্ধ করুন','🔴 Turn Off'):L($lang,'🟢 চালু করুন','🟢 Turn On');
    $src=$t['_source']??'legacy'; $id=$t['id'];
    $kb=[
        [['text'=>$toggle,'callback_data'=>"oldtsk_toggle_{$id}"]],
        [['text'=>'✏️ নাম এডিট','callback_data'=>"oldtsk_edit_name_{$id}"],['text'=>'✏️ রিওয়ার্ড এডিট','callback_data'=>"oldtsk_edit_reward_{$id}"]],
        [['text'=>'✏️ পাসওয়ার্ড এডিট','callback_data'=>"oldtsk_edit_password_{$id}"]],
        [['text'=>'🎥 কাজের ভিডিও সেট','callback_data'=>"oldtsk_setvideo_{$id}"]],
        [['text'=>'🗑️ ডিলিট করুন','callback_data'=>"oldtsk_del_{$id}"]],
        [['text'=>'🔙 তালিকায় ফিরে যান','callback_data'=>'list_tsk']]
    ];
    $text=pe($t['emoji'],'5397916757333654639')." <b>".L($lang,'কাজ বিস্তারিত','Task Details').":</b>\n\n"
        ."🏷️ ".L($lang,'নাম','Name').": <b>".htmlspecialchars($t['name'])."</b>\n"
        ."💰 ".L($lang,'রিওয়ার্ড','Reward').": ৳<b>".number_format($t['reward'],2)."</b>\n"
        ."🔐 ".L($lang,'পাসওয়ার্ড','Password').": <code>".htmlspecialchars($t['password'])."</code>\n"
        ."📊 ".L($lang,'স্ট্যাটাস','Status').": {$status}\n"
        ."🎥 ".L($lang,'ভিডিও','Video').": ".(getTaskVideo($t) ? 'সেট করা আছে' : 'সেট করা নেই')."\n"
        ."📥 ".L($lang,'Central Sheet থেকে CSV ডাউনলোড করা যাবে।','CSV can be downloaded from Central Sheet.');
    return ['text'=>$text,'reply_markup'=>json_encode(['inline_keyboard'=>$kb])];
}


function taskDetailView($t, $lang = 'bn') {
    $status_text = (($t['status'] ?? 'open') == 'open') ? L($lang, '🟢 চালু', '🟢 On') : L($lang, '🔴 বন্ধ', '🔴 Off');
    $toggle_label = (($t['status'] ?? 'open') == 'open') ? L($lang, '🔴 বন্ধ করুন', '🔴 Turn Off') : L($lang, '🟢 চালু করুন', '🟢 Turn On');
    
    $field_labels = taskFieldLabels();
    $field_names = [];
    foreach (($t['input_fields'] ?? []) as $f) {
        if (isset($field_labels[$f])) {
            $field_names[] = $field_labels[$f]['emoji'] . ' ' . $field_labels[$f][$lang];
        }
    }
    $fields_str = implode(', ', $field_names);
    
    $inline_kb = [
        [['text' => $toggle_label, 'callback_data' => "tsk_toggle_{$t['id']}"]],
        [
            ['text' => L($lang, '✏️ নাম এডিট', '✏️ Edit Name'), 'callback_data' => "tsk_editfield_name_{$t['id']}"],
            ['text' => L($lang, '✏️ Emoji এডিট', '✏️ Edit Emoji'), 'callback_data' => "tsk_editfield_emoji_{$t['id']}"]
        ],
        [
            ['text' => L($lang, '✏️ রিওয়ার্ড এডিট', '✏️ Edit Reward'), 'callback_data' => "tsk_editfield_reward_{$t['id']}"],
            ['text' => L($lang, '✏️ পাসওয়ার্ড এডিট', '✏️ Edit Password'), 'callback_data' => "tsk_editfield_password_{$t['id']}"]
        ],
        [
            ['text' => L($lang, '✏️ নির্দেশনা এডিট', '✏️ Edit Instruction'), 'callback_data' => "tsk_editfield_instruction_{$t['id']}"],
            ['text' => L($lang, '✏️ UID লিমিট', '✏️ UID Limit'), 'callback_data' => "tsk_editfield_uidlimit_{$t['id']}"]
        ],
        [['text' => L($lang, '📝 ইনপুট ফিল্ড পরিবর্তন', '📝 Change Input Fields'), 'callback_data' => "tsk_editfields_{$t['id']}"]],
    ];
    // যেসব user-input field সত্যিই সিলেক্ট করা আছে, শুধু সেগুলোর button edit অপশন দেখাবে.
    foreach (['uid','cookies','2fa'] as $bf) {
        if (in_array($bf, ($t['input_fields'] ?? []), true)) {
            $cap = strtoupper($bf);
            $inline_kb[] = [['text' => L($lang, "✏️ {$cap} বাটন এডিট", "✏️ Edit {$cap} Button"), 'callback_data' => "tsk_editbutton_{$bf}_{$t['id']}"]];
        }
    }
    $inline_kb[] = [['text' => L($lang, '🎥 কাজের ভিডিও সেট', '🎥 Set Task Video'), 'callback_data' => "tsk_setvideo_{$t['id']}"]];
    $inline_kb[] = [['text' => L($lang, '🗑️ ডিলিট করুন', '🗑️ Delete'), 'callback_data' => "tsk_del_{$t['id']}"]];
    $inline_kb[] = [['text' => L($lang, '🔙 তালিকায় ফিরে যান', '🔙 Back to List'), 'callback_data' => 'list_tsk']];

    
    $text = pe('🆕', '5397916757333654639') . " <b>" . L($lang, 'কাজ বিস্তারিত', 'Task Details') . ":</b>\n\n"
          . "🏷️ " . L($lang, 'নাম', 'Name') . ": <b>{$t['name']}</b>\n"
          . "{$t['emoji']} Emoji: <code>{$t['emoji']}</code>\n"
          . "💰 " . L($lang, 'রিওয়ার্ড', 'Reward') . ": ৳<b>" . number_format($t['reward'], 2) . "</b>\n"
          . "🔐 " . L($lang, 'পাসওয়ার্ড', 'Password') . ": <code>{$t['password']}</code>\n"
          . "📝 " . L($lang, 'ইনপুট ফিল্ড', 'Input Fields') . ": {$fields_str}\n"
          . "🔘 " . L($lang, 'ইনপুট বাটন', 'Input Buttons') . ": " . htmlspecialchars(implode(' | ', array_map(function($f) use ($t,$lang) { return taskFieldButtonLabel($t,$f,$lang); }, array_values(array_intersect(($t['input_fields'] ?? []), ['uid','2fa','cookies']))))) . "\n"
          . "🆔 " . L($lang, 'UID লিমিট', 'UID Limit') . ": " . ($t['uid_min_length'] ?? 10) . "-" . ($t['uid_max_length'] ?? 16) . "\n"
          . "📌 " . L($lang, 'নির্দেশনা', 'Instruction') . ": " . (!empty($t['instruction']) ? $t['instruction'] : '—') . "\n"
          . "📊 " . L($lang, 'স্ট্যাটাস', 'Status') . ": {$status_text}\n"
          . "🎥 " . L($lang, 'ভিডিও', 'Video') . ": " . (getTaskVideo($t) ? L($lang, 'সেট করা আছে', 'Set') : L($lang, 'সেট করা নেই', 'Not set'));
    
    return ['text' => $text, 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])];
}

function canonicalTaskFields($task) {
    // Every custom task sheet follows: identity -> Password -> task data.
    // This keeps Password in the middle, e.g. Username, Password, 2FA/Cookies
    // or UID, Password, 2FA/Cookies.
    $fields = array_values(array_unique($task['input_fields'] ?? []));
    $identity = ['username', 'email', 'fb_name', 'uid'];
    $result = [];

    foreach ($identity as $f) {
        if (in_array($f, $fields, true)) $result[] = $f;
    }

    // Password is always present in the sheet for every custom task.
    $result[] = 'password';

    $special = ['2fa', 'cookies'];
    foreach ($special as $f) {
        if (in_array($f, $fields, true)) $result[] = $f;
    }

    foreach ($fields as $f) {
        if (in_array($f, $identity, true) || $f === 'password' || in_array($f, $special, true)) continue;
        $result[] = $f;
    }
    return $result;
}

function taskFieldHeaders($task) {
    $map = [
        'username' => 'Username',
        'password' => 'Password',
        '2fa' => '2FA',
        'email' => 'Email',
        'name' => 'Name',
        'uid' => 'UID',
        'cookies' => 'Cookies',
        'first_name' => 'First Name',
        'last_name' => 'Last Name'
    ];
    $headers = [];
    foreach (canonicalTaskFields($task) as $f) {
        if ($f === 'fb_name') {
            $headers[] = 'First Name';
            $headers[] = 'Last Name';
        } elseif (isset($map[$f])) {
            $headers[] = $map[$f];
        }
    }
    return $headers ?: ['Data'];
}

function ensureTaskCsv($task) {
    $path = taskCsvPath($task['id']);
    if (file_exists($path)) return;
    $fh = fopen($path, 'w');
    if ($fh) {
        fputcsv($fh, taskFieldHeaders($task));
        fclose($fh);
    }
}

function normalizeTaskCsv($task) {
    $path = taskCsvPath($task['id']);
    if (!file_exists($path)) {
        ensureTaskCsv($task);
        return;
    }
    $in = fopen($path, 'r');
    if (!$in) return;
    $oldHeader = fgetcsv($in);
    if ($oldHeader === false) { fclose($in); return; }

    $rows = [];
    while (($row = fgetcsv($in)) !== false) {
        $assoc = [];
        foreach ($oldHeader as $i => $h) $assoc[trim((string)$h)] = $row[$i] ?? '';
        $outRow = [];
        foreach (canonicalTaskFields($task) as $f) {
            if ($f === 'fb_name') {
                $outRow[] = $assoc['First Name'] ?? '';
                $outRow[] = $assoc['Last Name'] ?? '';
            } else {
                $header = ['username'=>'Username','email'=>'Email','uid'=>'UID','password'=>'Password','2fa'=>'2FA','cookies'=>'Cookies','name'=>'Name','first_name'=>'First Name','last_name'=>'Last Name'][$f] ?? $f;
                $outRow[] = $assoc[$header] ?? '';
            }
        }
        $rows[] = $outRow;
    }
    fclose($in);

    $out = fopen($path, 'w');
    if (!$out) return;
    fputcsv($out, taskFieldHeaders($task));
    foreach ($rows as $r) fputcsv($out, $r);
    fclose($out);
}

function startDynamicTask($uid, $chat_id, $task, $lang, $users) {
    $temp_data = [
        'task_id' => $task['id'],
        'collected' => [],
        'remaining_fields' => [],
        'active_field' => '',
        'msg_id' => ''
    ];

    foreach (($task['input_fields'] ?? []) as $field) {
        if ($field === 'username') {
            $temp_data['collected']['username'] = generateUsername();
        } elseif ($field === 'email') {
            $g = generateGmailData();
            $temp_data['collected']['name'] = $g['name'];
            $temp_data['collected']['email'] = $g['email'];
        } elseif ($field === 'fb_name') {
            $f = generateFbNameArray();
            $temp_data['collected']['first_name'] = $f['first'];
            $temp_data['collected']['last_name'] = $f['last'];
        } elseif ($field === 'password') {
            $temp_data['collected']['password'] = $task['password'];
        } elseif (in_array($field, ['uid','2fa','cookies'], true)) {
            $temp_data['remaining_fields'][] = $field;
        }
    }

    $reward_fmt = number_format($task['reward'], 2);
    $preview = pe($task['emoji'], '5397916757333654639') . " <b>{$task['name']} (৳{$reward_fmt})</b>\n\n";

    $field_labels_display = [
        'username' => '👤 Username',
        'email' => '📧 Email',
        'name' => '👤 Name',
        'first_name' => '👤 First Name',
        'last_name' => '👤 Last Name'
    ];
    foreach ($temp_data['collected'] as $k => $v) {
        if (isset($field_labels_display[$k])) {
            $preview .= "{$field_labels_display[$k]}: <code>" . htmlspecialchars((string)$v) . "</code>\n";
        }
    }

    if (!empty($task['password'])) {
        $preview .= "🔐 " . L($lang, "পাসওয়ার্ড", "Password") . ": <code>" . htmlspecialchars((string)$task['password']) . "</code>\n";
    }
    if (!empty($task['instruction'])) {
        $preview .= "\n📌 <b>" . L($lang, "নির্দেশনা:", "Instructions:") . "</b>\n{$task['instruction']}\n";
    }
    if (!empty($temp_data['remaining_fields'])) {
        $preview .= "\n👇 " . L($lang, "প্রয়োজনীয় তথ্য দিতে নিচের বাটন থেকে একটি নির্বাচন করুন।", "Choose a button below to provide the required information.");
    } else {
        $preview .= "\n✅ " . L($lang, "কাজ সাবমিট করতে নিচের বাটনে চাপুন।", "Tap below to submit the task.");
    }

    $menu_rows = [];
    $video_kb = taskVideoButton(getTaskVideo($task), $lang);
    foreach ($video_kb as $vr) $menu_rows[] = $vr;
    $button_markup = taskInputButtonKeyboard($task, $temp_data['remaining_fields'], $lang, empty($temp_data['remaining_fields']));
    $button_obj = json_decode($button_markup, true);
    foreach (($button_obj['keyboard'] ?? []) as $r) $menu_rows[] = $r;

    $res = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => $preview,
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu_rows, 'resize_keyboard' => true])
    ]);
    $temp_data['msg_id'] = $res['result']['message_id'] ?? '';

    if (!empty($temp_data['remaining_fields'])) {
        setState($uid, 'dyn_task_choose', json_encode($temp_data));
    } else {
        setState($uid, 'dyn_task_confirm', json_encode($temp_data));
    }
}

function submitDynamicTask($uid, $chat_id, $temp_json, $users, $s, $lang) {
    $temp_data = json_decode($temp_json, true);
    if (!is_array($temp_data)) return false;
    
    $task = findTaskById(readTasks(), $temp_data['task_id']);
    if ($task === null) return false;
    
    $collected = $temp_data['collected'];
    
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $uid,
        'task_type' => 'dynamic',
        'task_id' => $task['id'],
        'task_name' => $task['name'],
        'data' => $collected,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    ensureTaskCsv($task);
    $csv_path = taskCsvPath($task['id']);
    $row = [];
    foreach (canonicalTaskFields($task) as $f) {
        if ($f === 'fb_name') {
            $row[] = $collected['first_name'] ?? '';
            $row[] = $collected['last_name'] ?? '';
        } elseif ($f === 'password') {
            // Always put the task's common password in the middle column.
            $row[] = $task['password'] ?? '';
        } elseif ($f === '2fa') {
            $row[] = str_replace(' ', '', $collected['2fa'] ?? '');
        } else {
            $row[] = $collected[$f] ?? '';
        }
    }
    $fh = fopen($csv_path, 'a');
    fputcsv($fh, $row);
    fclose($fh);
    
    if (!empty($task['sheet_url'])) {
        $unique_key = $collected['username'] ?? $collected['email'] ?? $collected['uid'] ?? '';
        if (!seedIsDuplicate($task['id'], $unique_key)) {
            $res = seedPushToSheet($task['sheet_url'], $task['sheet_name'] ?? $task['name'], $row);
            if ($res['ok'] ?? false) {
                seedMarkDuplicate($task['id'], $unique_key);
            }
        }
    }
    
    $users[$uid]['review_tasks'] = ($users[$uid]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($temp_data['msg_id'])) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $temp_data['msg_id']]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('✅', '5206607081334906820') . " " . L($lang, "আপনার কাজটি সফলভাবে জমা হয়েছে এবং রিভিউতে পাঠানো হলো।", "Your task has been submitted successfully and sent for review."),
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($uid)
    ]);
    setState($uid, "");
    return true;
}

// === অংশ ১/৩ শেষ ===
// পরের মেসেজে অংশ ২/৩ আসবে

# ==================== PART SEPARATOR ====================

// === অংশ ২/৩ শুরু ===

$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? null; 
$callback = $update['callback_query'] ?? null;
$chat_id = $message['chat']['id'] ?? $callback['message']['chat']['id'] ?? null;
$from_id = $message['from']['id'] ?? $callback['from']['id'] ?? null;
$text = $message['text'] ?? ""; 
$name = $message['from']['first_name'] ?? $callback['from']['first_name'] ?? "User";
$safe_name = htmlspecialchars($name);

if (!$chat_id) exit;

checkAutoBackup($s, ADMIN_ID);

$users = readDB('users.json');
$ref_by = 0; 
if (strpos($text, '/start ') === 0) { 
    $ref_by = trim(str_replace('/start ', '', $text)); 
}

$tg_username = $message['from']['username'] ?? $callback['from']['username'] ?? "";
$username_changed = false;

if (!isset($users[$from_id]) || !is_array($users[$from_id])) {
    $users[$from_id] = [
        'name' => $name, 
        'username' => $tg_username,
        'balance' => 0.00, 
        'pending_withdraw' => 0.00,
        'total_income' => 0.00,
        'completed_tasks' => 0,
        'review_tasks' => 0,
        'rejected_tasks' => 0, 
        'ref_by' => $ref_by, 
        'total_refs' => 0,
        'ref_income' => 0.00,
        'state' => '', 
        'temp' => '',
        'language' => 'bn'
    ];
    
    if (strval($ref_by) != "0" && strval($ref_by) != strval($from_id) && isset($users[$ref_by])) {
        $users[$ref_by]['total_refs'] = ($users[$ref_by]['total_refs'] ?? 0) + 1;
        $ref_by_lang = $users[$ref_by]['language'] ?? 'bn';
        bot('sendMessage', [
            'chat_id' => $ref_by, 
            'text' => pe('🎉', '5461151367559141950') . " " . L($ref_by_lang,
                "<b>নতুন রেফারাল!</b>\nএকজন আপনার রেফারে জয়েন করেছে। সে কাজ সম্পূর্ণ করলে আপনি নির্ধারিত কমিশন পাবেন।",
                "<b>New Referral!</b>\nSomeone joined using your referral. You'll earn the set commission once they complete a task."
            ), 
            'parse_mode' => 'HTML'
        ]);
    }
    $username_changed = true;
} else {
    if (!isset($users[$from_id]['username']) || $users[$from_id]['username'] !== $tg_username) {
        $users[$from_id]['username'] = $tg_username;
        $username_changed = true;
    }
}

if ($username_changed) {
    writeDB('users.json', $users);
}

$u = $users[$from_id]; 
$state = $u['state'] ?? ''; 
$temp = $u['temp'] ?? '';
$user_lang = $u['language'] ?? 'bn';

$menu_commands = [
    'Admin Dashboard', '⚙️ এডমিন প্যানেল', '🔙 এডমিন মেনু', '🔙 Admin Menu', 'বটের সেটিংস', '🔙 বটের সেটিংস', '🔙 Bot Settings', 
    '🔙 ফিরে যান', 'বাতিল', '🔙 Back', 'Cancel', '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet', 'কাজ ▸', 'ব্যালেন্স', 
    'টাকা উত্তোলন', 'My Referrals', 'আমি নতুন', 'সাপোর্ট',
    'ইনস্টাগ্রাম কন্ট্রোল', 'জিমেইল কন্ট্রোল', 'ফেসবুক কন্ট্রোল', 'কুকিজ কন্ট্রোল', 'ইনস্টা কুকিজ কন্ট্রোল',
    'ডাটাবেজ ক্লিনআপ', 'ব্রডকাস্ট', 'কাজ চালু/বন্ধ', 'পেমেন্ট অন/অফ', 'Task On/Off', 'Payment On/Off',
    'Instagram Control', 'Gmail Control', 'Facebook Control', 'Cookies Control', 'Insta Cookies Control', 'Database Cleanup',
    '🟢 ইনস্টা চালু করুন', '🔴 ইনস্টা বন্ধ করুন', '🟢 জিমেইল চালু করুন', '🔴 জিমেইল বন্ধ করুন', '🟢 ফেসবুক চালু করুন', '🔴 ফেসবুক বন্ধ করুন',
    '🟢 কুকিজ চালু করুন', '🔴 কুকিজ বন্ধ করুন', '🟢 ইনস্টা কুকিজ চালু করুন', '🔴 ইনস্টা কুকিজ বন্ধ করুন',
    '🟢 Turn On Instagram', '🔴 Turn Off Instagram', '🟢 Turn On Gmail', '🔴 Turn Off Gmail', '🟢 Turn On Facebook', '🔴 Turn Off Facebook',
    '🟢 Turn On Cookies', '🔴 Turn Off Cookies', '🟢 Turn On Insta Cookies', '🔴 Turn Off Insta Cookies',
    'উইথড্র মেথড সেটিংস', 'নতুন মেথড যোগ করুন', '📋 সব মেথড দেখুন',
    'জয়েন চ্যানেল ম্যানেজমেন্ট', 'Join Channel Management', 'নতুন চ্যানেল যোগ করুন', '📋 সব চ্যানেল দেখুন', 'Language / ভাষা',
    '📊 সিড কন্ট্রোল', '🔙 সিড কন্ট্রোল', '💾 ব্যাকআপ', '🔙 ব্যাকআপ',
    '🆕 কাজ ম্যানেজমেন্ট', '🆕 Task Management', '➕ নতুন কাজ যোগ করুন', '➕ Add New Task',
    '📋 সব কাজ দেখুন', '📋 View All Tasks',
];

if (in_array($text, $menu_commands)) {
    setState($from_id, "");
    $state = "";
    $temp = "";
}

if (strval($from_id) !== strval(ADMIN_ID)) {
    $all_channels_to_check = [];
    if (!empty($s['force_channel'])) {
        $all_channels_to_check[] = [
            'id' => 'legacy',
            'title' => 'অফিশিয়াল চ্যানেল',
            'chat_id' => $s['force_channel'],
            'url' => $s['force_channel_url'] ?? '',
            'color' => '📢'
        ];
    }
    foreach (($s['join_channels'] ?? []) as $jc) {
        if (($jc['status'] ?? 'open') == 'open') {
            $all_channels_to_check[] = $jc;
        }
    }

    if (!empty($all_channels_to_check)) {
        $jc_lang = $users[$from_id]['language'] ?? 'bn';
        $not_joined = [];
        foreach ($all_channels_to_check as $ch_item) {
            if (empty($ch_item['chat_id'])) continue;
            $check_chat_id = trim((string)$ch_item['chat_id']);
            if (preg_match('~^https?://t\.me/([^/?#]+)~i', $check_chat_id, $m)) {
                $check_chat_id = '@' . ltrim($m[1], '@');
            } elseif (preg_match('~^t\.me/([^/?#]+)~i', $check_chat_id, $m)) {
                $check_chat_id = '@' . ltrim($m[1], '@');
            } elseif ($check_chat_id !== '' && $check_chat_id[0] !== '@' && !preg_match('/^-?\d+$/', $check_chat_id)) {
                $check_chat_id = '@' . $check_chat_id;
            }
            $res = bot('getChatMember', ['chat_id' => $check_chat_id, 'user_id' => $from_id]);
            $mstatus = 'left';
            if (isset($res['ok']) && $res['ok'] == true) {
                $mstatus = $res['result']['status'] ?? 'left';
            }
            if (!in_array($mstatus, ['member', 'administrator', 'creator'])) {
                $not_joined[] = $ch_item;
            }
        }

        if (!empty($not_joined)) {
            if ($callback && $callback['data'] == 'check_force_join') {
                bot('answerCallbackQuery', ['callback_query_id' => $callback['id'], 'text' => t('not_joined_alert', $jc_lang), 'show_alert' => true]);
                exit;
            }

            $inline_kb = [];
            foreach ($not_joined as $ch_item) {
                if (empty($ch_item['url'])) continue;
                $color = $ch_item['color'] ?? '📢';
                $inline_kb[] = [['text' => "{$color} {$ch_item['title']}", 'url' => $ch_item['url']]];
            }
            $inline_kb[] = [['text' => t('joined_btn', $jc_lang), 'callback_data' => 'check_force_join']];

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('⚠️', '5447644880824181073') . " <b>" . t('must_join_title', $jc_lang) . " {$safe_name},</b>\n\n" . t('must_join_body', $jc_lang),
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
            ]);
            exit;
        }
    }
}

if ($callback && $callback['data'] == 'check_force_join') {
    bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $callback['message']['message_id']]);
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('🙂', '5461117441612462242') . " " . t('joined_success', $users[$from_id]['language'] ?? 'bn'), 
        'reply_markup' => getMainMenu($from_id)
    ]);
    exit;
}

if (strpos($text, '/start') === 0 || $text == '🔙 ফিরে যান' || $text == 'বাতিল' || ($text == '🔙 সেন্ট্রাল শিট' || $text == '🔙 Central Sheet') || $text == '🔙 Back' || $text == 'Cancel') {
    setState($from_id, "");
    $user_lang = $users[$from_id]['language'] ?? 'bn';
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('🙂', '5461117441612462242') . " " . t('welcome', $user_lang) . pe('⬇️', '5406745015365943482'), 
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    exit;
}

if ($text == 'Language / ভাষা') {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🌐', '5253742260054409879') . " " . t('language_prompt', $users[$from_id]['language'] ?? 'bn'),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => [
            [
                ['text' => '🇧🇩 বাংলা', 'callback_data' => 'lang_set_bn'],
                ['text' => '🇬🇧 English', 'callback_data' => 'lang_set_en']
            ]
        ]])
    ]);
    exit;
}

// ============================================================
// 🎯 কাজ ▸ মেনু (পুরনো ৫টি + নতুন ডাইনামিক সব একসাথে)
// ============================================================
if ($text == 'কাজ ▸') {
    $ja_lang = $users[$from_id]['language'] ?? 'bn';
    $menu = [];
    $row = [];
    
    // ১️⃣ পুরনো ৫টি হার্ডকোড কাজ
    if (($s['status_instagram'] ?? 'open') === 'open' && empty($s['deleted_legacy_instagram'])) {
        $r = number_format($s['reward_instagram'], 2);
        $row[] = makeBtn(($s['custom_name_legacy_instagram'] ?? t('insta_2fa_label', $ja_lang)) . " (৳{$r})", 'success');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    if (($s['status_gmail'] ?? 'open') === 'open' && empty($s['deleted_legacy_gmail'])) {
        $r = number_format($s['reward_gmail'], 2);
        $row[] = makeBtn(($s['custom_name_legacy_gmail'] ?? t('gmail_label', $ja_lang)) . " (৳{$r})", 'primary');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    if (($s['status_facebook'] ?? 'open') === 'open' && empty($s['deleted_legacy_facebook'])) {
        $r = number_format($s['reward_facebook'], 2);
        $row[] = makeBtn(($s['custom_name_legacy_facebook'] ?? t('fb_2fa_label', $ja_lang)) . " (৳{$r})", 'success');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    if (($s['status_facebook_cookies'] ?? 'open') === 'open' && empty($s['deleted_legacy_facebook_cookies'])) {
        $r = number_format($s['reward_facebook_cookies'] ?? 8.00, 2);
        $row[] = makeBtn(($s['custom_name_legacy_facebook_cookies'] ?? t('fb_cookies_label', $ja_lang)) . " (৳{$r})", 'primary');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    if (($s['status_instagram_cookies'] ?? 'open') === 'open' && empty($s['deleted_legacy_instagram_cookies'])) {
        $r = number_format($s['reward_instagram_cookies'] ?? 8.00, 2);
        $row[] = makeBtn(($s['custom_name_legacy_instagram_cookies'] ?? t('insta_cookies_label', $ja_lang)) . " (৳{$r})", 'success');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    // ২️⃣ নতুন কাস্টম কাজ
    $custom_tasks = readTasks();
    foreach ($custom_tasks as $task) {
        if (($task['status'] ?? 'open') !== 'open') continue;
        $emoji = $task['emoji'] ?? '🆕';
        $label = "{$emoji} {$task['name']} (৳" . number_format($task['reward'], 2) . ")";
        $row[] = makeBtn($label, 'success');
        if (count($row) == 2) { $menu[] = $row; $row = []; }
    }
    
    if (!empty($row)) $menu[] = $row;
    $menu[] = [makeBtn(L($ja_lang, 'বাতিল', 'Cancel'), 'danger')];
    
    if (count($menu) === 1) {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚠️', '5447644880824181073') . " " . L($ja_lang, "দুঃখিত, বর্তমানে কোনো কাজ চালু নেই।", "Sorry, no tasks are currently open."),
            'parse_mode' => 'HTML',
            'reply_markup' => getMainMenu($from_id)
        ]);
        exit;
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🎯', '5224607267797606837') . " " . L($ja_lang, "নিচের তালিকা থেকে একটি কাজ সিলেক্ট করুন:", "Select a task from the list below:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    exit;
}

// পুরোনো ৫টি কাজের custom নামকে legacy handler-এর canonical নামে map করা
$legacy_name_map = [
    $s['custom_name_legacy_instagram'] ?? t('insta_2fa_label',$user_lang) => t('insta_2fa_label',$user_lang),
    $s['custom_name_legacy_gmail'] ?? t('gmail_label',$user_lang) => t('gmail_label',$user_lang),
    $s['custom_name_legacy_facebook'] ?? t('fb_2fa_label',$user_lang) => t('fb_2fa_label',$user_lang),
    $s['custom_name_legacy_facebook_cookies'] ?? t('fb_cookies_label',$user_lang) => t('fb_cookies_label',$user_lang),
    $s['custom_name_legacy_instagram_cookies'] ?? t('insta_cookies_label',$user_lang) => t('insta_cookies_label',$user_lang)
];
if (isset($legacy_name_map[$text]) && $legacy_name_map[$text] !== $text) { $text = $legacy_name_map[$text]; }

// ============================================================
// 🆕 ডাইনামিক টাস্ক হ্যান্ডলার
// ============================================================
$dyn_tasks = readTasks();
$matched_task = null;
foreach ($dyn_tasks as $dt) {
    if (strpos($text, $dt['name']) !== false && strpos($text, '(৳') !== false) {
        $matched_task = $dt;
        break;
    }
}

if ($matched_task !== null) {
    if (($matched_task['status'] ?? 'open') !== 'open') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, এই কাজটি বর্তমানে বন্ধ আছে।", "Sorry, this task is currently closed."),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    startDynamicTask($from_id, $chat_id, $matched_task, $user_lang, $users);
    exit;
}

// ডাইনামিক: প্রথমে কোন তথ্যের বাটনে ক্লিক করেছে তা নির্বাচন
if ($state == 'dyn_task_choose' && $text != 'বাতিল' && $text != 'Cancel') {
    $temp_data = json_decode($temp, true);
    if (!is_array($temp_data)) { setState($from_id, ''); exit; }
    $task = findTaskById(readTasks(), $temp_data['task_id'] ?? '');
    if ($task === null) { setState($from_id, ''); exit; }

    $selected_field = null;
    foreach (($temp_data['remaining_fields'] ?? []) as $f) {
        if ($text === taskFieldButtonLabel($task, $f, $user_lang)) { $selected_field = $f; break; }
    }
    if ($selected_field === null) exit;

    $temp_data['active_field'] = $selected_field;
    setState($from_id, 'dyn_task_input', json_encode($temp_data));
    $field_map = [
        'uid' => ['bn'=>'UID','en'=>'UID','emoji'=>'🆔'],
        '2fa' => ['bn'=>'2FA Key','en'=>'2FA Key','emoji'=>'🔑'],
        'cookies' => ['bn'=>'Cookies','en'=>'Cookies','emoji'=>'🍪']
    ];
    $fl = $field_map[$selected_field];
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe($fl['emoji'], '5397916757333654639') . ' ' . L($user_lang, "এখন আপনার {$fl['bn']} পাঠান:", "Now send your {$fl['en']}:") . "\n\n" . L($user_lang, "নিচের বাতিল বাটন ব্যবহার করতে পারেন।", "You can use the Cancel button below."),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    exit;
}

// ডাইনামিক: নির্বাচিত বাটনের তথ্য গ্রহণ
if ($state == 'dyn_task_input' && $text != 'বাতিল' && $text != 'Cancel') {
    $temp_data = json_decode($temp, true);
    if (!is_array($temp_data) || empty($temp_data['active_field'])) { setState($from_id, ''); exit; }

    $next_field = $temp_data['active_field'];
    $input_val = trim($text);
    $task = findTaskById(readTasks(), $temp_data['task_id'] ?? '');
    if ($task === null) { setState($from_id, ''); exit; }

    if ($next_field === 'uid') {
        $min_len = intval($task['uid_min_length'] ?? 10);
        $max_len = intval($task['uid_max_length'] ?? 16);
        $len = strlen($input_val);
        if (!ctype_digit($input_val) || $len < $min_len || $len > $max_len) {
            bot('sendMessage', ['chat_id'=>$chat_id,'text'=>pe('❌','5210952531676504517').' '.L($user_lang,"UID টি সঠিক নয়!\n\nUID অবশ্যই <b>{$min_len}</b> থেকে <b>{$max_len}</b> ডিজিটের শুধু সংখ্যা হতে হবে।\n\nআবার সঠিক UID পাঠান:","UID is incorrect!\n\nUID must be <b>{$min_len}</b> to <b>{$max_len}</b> digits.\n\nSend correct UID again:"),'parse_mode'=>'HTML']);
            exit;
        }
    } elseif ($next_field === '2fa') {
        if (!isValidBase32Key($input_val)) {
            bot('sendMessage', ['chat_id'=>$chat_id,'text'=>pe('❌','5210952531676504517').' '.L($user_lang,"2FA কী সঠিক নয়, আবার পাঠান:","Invalid 2FA key, send again:"),'parse_mode'=>'HTML']);
            exit;
        }
        $input_val = str_replace(' ', '', $input_val);
    } elseif ($next_field === 'cookies') {
        if (strlen($input_val) < 10) {
            bot('sendMessage', ['chat_id'=>$chat_id,'text'=>pe('❌','5210952531676504517').' '.L($user_lang,"Cookies অনেক ছোট, সঠিক ডাটা পাঠান:","Cookies too short, send valid data:"),'parse_mode'=>'HTML']);
            exit;
        }
    }

    $temp_data['collected'][$next_field] = $input_val;
    $temp_data['remaining_fields'] = array_values(array_diff(($temp_data['remaining_fields'] ?? []), [$next_field]));
    $temp_data['active_field'] = '';

    if (!empty($temp_data['remaining_fields'])) {
        setState($from_id, 'dyn_task_choose', json_encode($temp_data));
        $rows = [];
        $video_kb = taskVideoButton(getTaskVideo($task), $user_lang);
        foreach ($video_kb as $vr) $rows[] = $vr;
        $mk = json_decode(taskInputButtonKeyboard($task, $temp_data['remaining_fields'], $user_lang, false), true);
        foreach (($mk['keyboard'] ?? []) as $r) $rows[] = $r;
        bot('sendMessage', [
            'chat_id'=>$chat_id,
            'text'=>pe('✅','5206607081334906820').' '.L($user_lang,"তথ্যটি রেকর্ড হয়েছে।\n\nআরও তথ্য থাকলে নিচের বাটন থেকে নির্বাচন করুন:","Information recorded.\n\nIf more information is needed, choose another button below:"),
            'parse_mode'=>'HTML','reply_markup'=>json_encode(['keyboard'=>$rows,'resize_keyboard'=>true])
        ]);
        exit;
    }

    // সব প্রয়োজনীয় তথ্য দেওয়া হলে এখনই 2FA/UID/Cookies নিয়ে কোনো অতিরিক্ত prompt নয়; শুধু submit button দেখাবে।
    $summary = pe($task['emoji'] ?? '🆕','5397916757333654639')." <b>{$task['name']}</b>\n\n";
    if (!empty($task['password'])) $summary .= "🔐 ".L($user_lang,"পাসওয়ার্ড","Password").": <code>".htmlspecialchars((string)$task['password'])."</code>\n";
    $summary .= "\n<b>".L($user_lang,"সাবমিট হবে:","Will submit:")."</b>\n";
    foreach ($temp_data['collected'] as $k=>$v) {
        if (in_array($k,['first_name','last_name','name','email','username'],true)) continue;
        $label = strtoupper($k);
        $display_v = ($k === 'cookies' || $k === '2fa') ? substr((string)$v,0,30).'...' : $v;
        $summary .= "🔹 {$label}: <code>".htmlspecialchars((string)$display_v)."</code>\n";
    }
    $summary .= "\n".L($user_lang,"ফাইনাল সাবমিট করতে নিচের বাটনে চাপুন:","Tap below to finalize submission:");
    setState($from_id, 'dyn_task_confirm', json_encode($temp_data));
    bot('sendMessage', ['chat_id'=>$chat_id,'text'=>$summary,'parse_mode'=>'HTML','reply_markup'=>taskInputButtonKeyboard($task, [], $user_lang, true)]);
    exit;
}


if ($state == 'dyn_task_confirm' && ($text == '✅ কাজ সাবমিট' || $text == '✅ Submit Task')) {
    submitDynamicTask($from_id, $chat_id, $temp, $users, $s, $user_lang);
    exit;
}

// ------------------- ১. ইনস্টাগ্রাম 2FA কাজ -------------------
if ((strpos($text, 'ইনস্টা 2FA') !== false || strpos($text, 'ইনস্টাগ্রাম 2fa') !== false || strpos($text, 'ইনস্টাগ্রাম 2FA') !== false || strpos($text, 'Insta 2FA') !== false) && strpos($text, '(৳') !== false) {
    $legacy_video = getTaskVideo(getManagedTaskById('legacy_instagram'));
    if (!empty($legacy_video)) bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('🎥','5264919878082509254').' <b>কাজের ভিডিও:</b>','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>taskVideoButton($legacy_video,$user_lang)])]);

    if (($s['status_instagram'] ?? 'open') !== 'open') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে ইনস্টাগ্রামের কাজ বন্ধ আছে।", "Sorry, Instagram tasks are currently closed."), 'parse_mode' => 'HTML']);
        exit;
    }
    $gen_username = generateUsername();
    $admin_password = $s['admin_insta_password'] ?? 'Pass@insta';
    
    $menu = [
        [makeBtn('2FA Set', 'success')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    
    $msg_res = bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('🟢', '5416081784641168838') . " " . L($user_lang,
            "<b>আপনার জন্য ইনস্টাগ্রাম আইডি বরাদ্ধ করা হয়েছে!</b>\n\n👤 Username: <code>{$gen_username}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n📸 উপরের ইউজারনেম এবং পাসওয়ার্ড দিয়ে অ্যাকাউন্ট খুলুন এবং ২এফএ অন করুন। তারপর নিচে <b>🔐 2FA Set</b> বাটনে চাপ দিন।",
            "<b>An Instagram ID has been assigned to you!</b>\n\n👤 Username: <code>{$gen_username}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n📸 Create the account using the username and password above and turn on 2FA. Then tap the <b>🔐 2FA Set</b> button below."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    $msg_id = $msg_res['result']['message_id'] ?? '';
    
    setState($from_id, 'view_task', $gen_username . "|" . $admin_password . "|" . $msg_id);
    exit;
}

if ($text == '2FA Set' && $state == 'view_task') {
    $menu = [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]];
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('🔑', '5296369303661067030') . " " . L($user_lang, "এখন আপনার তৈরি করা অ্যাকাউন্টের সঠিক <b>2FA Key</b> টি এখানে লিখে দিন: ", "Now enter the correct <b>2FA Key</b> for the account you created: ") . pe('⤵️', '5447183459602669338'), 
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    setState($from_id, 'wait_2fa_key', $temp); 
    exit;
}

if ($state == 'wait_2fa_key' && $text != 'বাতিল' && $text != 'Cancel') {
    if (!isValidBase32Key($text)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "২এফএ কী (Key) ফরম্যাটটি সঠিক নয়!", "The 2FA Key format is not valid!"), 'parse_mode' => 'HTML']);
        exit;
    }
    
    $live_code = getTOTPWithOffset($text, 0);
    $next_code = getTOTPWithOffset($text, 30);
    
    if (!$live_code) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " ২এফএ কী (Key) টি সঠিক নয়। পুনরায় চেষ্টা করুন:", "The 2FA Key is incorrect. Please try again:"), 'parse_mode' => 'HTML']);
        exit;
    }
    
    setState($from_id, 'wait_work_otp_confirm', $temp . "|" . $text . "|" . $live_code);
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang,
            "<b>আপনার ২এফএ কী সফলভাবে যাচাই করা হয়েছে।</b>\n\n"
               . pe('🔔', '5458603043203327669') . " <b>অ্যাকাউন্ট খোলা শেষ হলে নিচের কোডগুলো ইনস্টাগ্রামে ট্রাই করুন:</b>\n"
               . "<i>(আপনার ঘড়ির সময় অনুযায়ী যেকোনো ১টি কোড কাজ করবে)</i>\n\n"
               . pe('🛡', '5251203410396458957') . " <b>কোড ১ (বর্তমান):</b> <code>{$live_code}</code>\n"
               . pe('⌛', '5386367538735104399') . " <b>কোড ২ (পরবর্তী ৩০ সেকেন্ড):</b> <code>{$next_code}</code>\n\n"
               . "কাজটি জমা দিতে নিচে <b>✅ কাজ শেষ</b> বাটনে ক্লিক করুন।",
            "<b>Your 2FA key has been verified successfully.</b>\n\n"
               . pe('🔔', '5458603043203327669') . " <b>Once account creation is done, try the codes below on Instagram:</b>\n"
               . "<i>(Whichever code matches your current clock time will work)</i>\n\n"
               . pe('🛡', '5251203410396458957') . " <b>Code 1 (current):</b> <code>{$live_code}</code>\n"
               . pe('⌛', '5386367538735104399') . " <b>Code 2 (next 30 seconds):</b> <code>{$next_code}</code>\n\n"
               . "Tap <b>✅ Job Done</b> below to submit the task."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'কাজ শেষ', 'Job Done'), 'success')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'wait_work_otp_confirm' && ($text == 'কাজ শেষ' || $text == 'Job Done')) {
    $parts = explode('|', $temp);
    $username = $parts[0];
    $password = $parts[1];
    $msg_id = $parts[2];
    $saved_key = $parts[3];
    
    $proof_id = uniqid();
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => $proof_id,
        'user_id' => $from_id,
        'task_type' => 'instagram',
        'username' => $username,
        'password' => $password,
        'data' => $saved_key,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $saved_key_clean = str_replace(' ', '', $saved_key);
    $csv_file = fopen("data/accounts_master.csv", "a");
    fputcsv($csv_file, [$username, $password, $saved_key_clean]);
    fclose($csv_file);
    
    seedSubmitTask($s, 'instagram', [
        'username' => $username,
        'password' => $password,
        '2fa' => $saved_key
    ]);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($msg_id)) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('✅', '5206607081334906820') . " " . L($user_lang, "আপনার কাজটি সফলভাবে জমা হয়েছে এবং রিভিউতে পাঠানো হলো।", "Your task has been submitted successfully and sent for review."), 
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

// ------------------- ২. জিমেইল কাজ -------------------
if ((strpos($text, 'জিমেইল কাজ') !== false || strpos($text, 'Gmail Task') !== false) && strpos($text, 'কন্ট্রোল') === false && strpos($text, '(৳') !== false) {
    $legacy_video = getTaskVideo(getManagedTaskById('legacy_gmail'));
    if (!empty($legacy_video)) bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('🎥','5264919878082509254').' <b>কাজের ভিডিও:</b>','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>taskVideoButton($legacy_video,$user_lang)])]);

    if (($s['status_gmail'] ?? 'open') !== 'open') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে জিমেইলের কাজ বন্ধ আছে।", "Sorry, Gmail tasks are currently closed."), 'parse_mode' => 'HTML']);
        exit;
    }
    $reward_gmail = number_format($s['reward_gmail'], 2);
    $gmail_data = generateGmailData();
    $admin_password = $s['admin_gmail_password'] ?? 'Pass@gmail';
    
    $menu = [
        [makeBtn(L($user_lang, '📤 কাজ সাবমিট করুন', '📤 Submit Task'), 'success')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    
    $msg_res = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('📧', '5253742260054409879') . " " . L($user_lang,
            "<b>আপনার জন্য নতুন জিমেইল কাজ বরাদ্ধ করা হয়েছে!</b>\n\n👤 Name: <code>{$gmail_data['name']}</code>\n✉️ Email: <code>{$gmail_data['email']}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " উপরের নাম, ইমেইল এবং পাসওয়ার্ড দিয়ে একটি জিমেইল অ্যাকাউন্ট তৈরি করুন। অ্যাকাউন্ট তৈরি শেষ হলে নিচে <b>📤 কাজ সাবমিট করুন</b> বাটনে চাপ দিন।",
            "<b>A new Gmail task has been assigned to you!</b>\n\n👤 Name: <code>{$gmail_data['name']}</code>\n✉️ Email: <code>{$gmail_data['email']}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " Create a Gmail account using the name, email and password above. Once the account is created, tap the <b>📤 Submit Task</b> button below."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    $msg_id = $msg_res['result']['message_id'] ?? '';
    
    setState($from_id, 'gmail_task_active', $gmail_data['name'] . "|" . $gmail_data['email'] . "|" . $admin_password . "|" . $msg_id);
    exit;
}

if ($state == 'gmail_task_active' && ($text == '📤 কাজ সাবমিট করুন' || $text == '📤 Submit Task')) {
    $parts = explode('|', $temp);
    $name = $parts[0];
    $email = $parts[1];
    $password = $parts[2];
    $msg_id = $parts[3];

    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'gmail',
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $csv_file = fopen("data/gmail_master.csv", "a");
    fputcsv($csv_file, [$name, $email, $password]);
    fclose($csv_file);
    
    seedSubmitTask($s, 'gmail', [
        'email' => $email,
        'password' => $password
    ]);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($msg_id)) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('✅', '5206607081334906820') . " " . L($user_lang,
            "আপনার জিমেইল কাজটি সফলভাবে সাবমিট হয়েছে!\n\n" . pe('⏳', '5386367538735104399') . " আগামী <b>২ থেকে ৩ ঘণ্টার মধ্যে</b> আপনার এই সাবমিট করা জিমেইলের ব্যালেন্সটি আপনার একাউন্টে যোগ করে দেওয়া হবে।",
            "Your Gmail task has been submitted successfully!\n\n" . pe('⏳', '5386367538735104399') . " The balance for this submitted Gmail will be added to your account within <b>2 to 3 hours</b>."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

// ------------------- ৩. ফেসবুক 2FA কাজ -------------------
if ((strpos($text, 'ফেসবুক 2FA') !== false || strpos($text, 'Facebook 2FA') !== false) && strpos($text, '(৳') !== false) {
    $legacy_video = getTaskVideo(getManagedTaskById('legacy_facebook'));
    if (!empty($legacy_video)) bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('🎥','5264919878082509254').' <b>কাজের ভিডিও:</b>','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>taskVideoButton($legacy_video,$user_lang)])]);

    if (($s['status_facebook'] ?? 'open') !== 'open') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে ফেসবুকের কাজ বন্ধ আছে।", "Sorry, Facebook tasks are currently closed."), 'parse_mode' => 'HTML']);
        exit;
    }
    $reward_fb = number_format($s['reward_facebook'], 2);
    $fb_name_arr = generateFbNameArray();
    $first_name = $fb_name_arr['first'];
    $last_name = $fb_name_arr['last'];
    $admin_password = $s['admin_fb_password'] ?? 'Pass@facebook';
    
    $menu = [
        [makeBtn(L($user_lang, '📤 ফেসবুক আইডি দিন', '📤 Submit Facebook ID'), 'success')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    
    $msg_res = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('👥', '5460795800101594035') . " " . L($user_lang,
            "<b>ফেসবুক অ্যাকাউন্ট তৈরির কাজ (৳{$reward_fb})</b>\n\n👤 First Name: <code>{$first_name}</code>\n👤 Last Name: <code>{$last_name}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " উপরের ফার্স্ট নেম, লাস্ট নেম এবং পাসওয়ার্ড দিয়ে একটি ফেসবুক অ্যাকাউন্ট তৈরি করুন এবং সেটির ২এফএ চালু করুন।\nএরপর নিচে <b>📤 ফেসবুক আইডি দিন</b> বাটনে ক্লিক করে ফেসবুক UID এবং 2FA Key সাবমিট করুন।",
            "<b>Facebook Account Creation Task (৳{$reward_fb})</b>\n\n👤 First Name: <code>{$first_name}</code>\n👤 Last Name: <code>{$last_name}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " Create a Facebook account using the first name, last name and password above, and turn on its 2FA.\nThen tap <b>📤 Submit Facebook ID</b> below and submit the Facebook UID and 2FA Key."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    $msg_id = $msg_res['result']['message_id'] ?? '';
    
    setState($from_id, 'fb_task_active', $first_name . "|" . $last_name . "|" . $admin_password . "|" . $msg_id);
    exit;
}

if (($text == '📤 ফেসবুক আইডি দিন' || $text == '📤 Submit Facebook ID') && $state == 'fb_task_active') {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🆔', '5231012545799666522') . " " . L($user_lang, " আপনার ফেসবুক অ্যাকাউন্টের <b>UID (ইউজার আইডি)</b> টি লিখে পাঠান:", "Enter the <b>UID (User ID)</b> of your Facebook account:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'fb_wait_uid', $temp);
    exit;
}

if ($state == 'fb_wait_uid' && $text != 'বাতিল' && $text != 'Cancel') {
    if (!ctype_digit($text) || strlen($text) < 9 || strlen($text) > 16) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " এটি একটি সঠিক ফেসবুক ইউআইডি (UID) নয়!\n\nফেসবুক ইউআইডি সাধারণত ৯ থেকে ১৬ ডিজিটের শুধুমাত্র সংখ্যা (Numbers) হয়ে থাকে। দয়া করে পুনরায় সঠিক আইডিটি পাঠান:", "This is not a valid Facebook UID!\n\nA Facebook UID is usually 9 to 16 digits, numbers only. Please send the correct ID again:"), 'parse_mode' => 'HTML']);
        exit;
    }
    setState($from_id, 'fb_wait_2fa', $temp . "|" . $text); 
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang, " আপনার ফেসবুক অ্যাকাউন্টের <b>2FA Key</b> টি লিখে পাঠান:", "Enter the <b>2FA Key</b> of your Facebook account:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'fb_wait_2fa' && $text != 'বাতিল' && $text != 'Cancel') {
    if (!isValidBase32Key($text)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " ফেসবুক ২এফএ কী ফরম্যাটটি সঠিক নয়। পুনরায় দিন:", "The Facebook 2FA key format is not correct. Please enter it again:"), 'parse_mode' => 'HTML']);
        exit;
    }
    $live_code = getTOTP($text);
    if (!$live_code) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " ২এফএ কী ভুল। পুনরায় দিন:", "The 2FA key is wrong. Please enter it again:"), 'parse_mode' => 'HTML']);
        exit;
    }
    
    setState($from_id, 'fb_wait_confirm', $temp . "|" . $text . "|" . $live_code);
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang,
            "ফেসবুক ২এফএ কী সফলভাবে যাচাই করা হয়েছে।\n\n" . pe('📌', '5397782960512444700') . " ২এফএ লাইভ কোড: <code>{$live_code}</code>\n\nকাজটি ফাইনাল সাবমিট করতে নিচের <b>✅ সাবমিট সম্পূর্ণ করুন</b> বাটনে চাপুন।",
            "Facebook 2FA key has been verified successfully.\n\n" . pe('📌', '5397782960512444700') . " 2FA live code: <code>{$live_code}</code>\n\nTap the <b>✅ Complete Submission</b> button below to finalize the submission."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'সাবমিট সম্পূর্ণ করুন', 'Complete Submission'), 'success')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'fb_wait_confirm' && ($text == 'সাবমিট সম্পূর্ণ করুন' || $text == 'Complete Submission')) {
    $parts = explode('|', $temp);
    $first_name = $parts[0];
    $last_name = $parts[1];
    $password = $parts[2];
    $msg_id = $parts[3];
    $fb_uid = $parts[4];
    $fb_2fa = $parts[5];
    
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'facebook',
        'uid' => $fb_uid,
        'password' => $password,
        'data' => $fb_2fa,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $fb_2fa_clean = str_replace(' ', '', $fb_2fa);
    $csv_file = fopen("data/facebook_master.csv", "a");
    fputcsv($csv_file, [$fb_uid, $password, $fb_2fa_clean]);
    fclose($csv_file);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($msg_id)) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('✅', '5206607081334906820') . " " . L($user_lang, " আপনার ফেসবুক কাজটি সফলভাবে সাবমিট হয়েছে এবং রিভিউতে পাঠানো হলো।", "Your Facebook task has been submitted successfully and sent for review."),
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

// ------------------- ৪. ফেসবুক কুকিজ কাজ -------------------
if ((strpos($text, 'FB কুকিজ') !== false || strpos($text, 'ফেসবুক কুকিজ') !== false || strpos($text, 'Facebook Cookies') !== false) && strpos($text, '(৳') !== false) {
    $legacy_video = getTaskVideo(getManagedTaskById('legacy_facebook_cookies'));
    if (!empty($legacy_video)) bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('🎥','5264919878082509254').' <b>কাজের ভিডিও:</b>','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>taskVideoButton($legacy_video,$user_lang)])]);

    if (($s['status_facebook_cookies'] ?? 'open') !== 'open') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে ফেসবুক কুকিজের কাজ বন্ধ আছে।", "Sorry, Facebook Cookies tasks are currently closed."), 'parse_mode' => 'HTML']);
        exit;
    }
    $reward_cookies = number_format($s['reward_facebook_cookies'] ?? 8.00, 2);
    $fb_name_arr = generateFbNameArray();
    $first_name = $fb_name_arr['first'];
    $last_name = $fb_name_arr['last'];
    $gen_fb_name = $first_name . " " . $last_name;
    $admin_password = $s['admin_fb_cookies_password'] ?? 'Pass@cookies';
    
    $menu = [
        [makeBtn(L($user_lang, '📤 ফেসবুক আইডি দিন', '📤 Submit Facebook ID'), 'success')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    
    $msg_res = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('👥', '5460795800101594035') . " " . L($user_lang,
            "<b>ফেসবুক কুকিজ তৈরির কাজ (৳{$reward_cookies})</b>\n\n👤 First Name: <code>{$first_name}</code>\n👤 Last Name: <code>{$last_name}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " উপরের ফার্স্ট নেম, লাস্ট নেম এবং পাসওয়ার্ড দিয়ে একটি ফেসবুক অ্যাকাউন্ট তৈরি করুন। অ্যাকাউন্ট তৈরি সম্পন্ন হলে নিচে <b>📤 ফেসবুক কুকিজ দিন</b> বাটনে ক্লিক করুন।",
            "<b>Facebook Cookies Creation Task (৳{$reward_cookies})</b>\n\n👤 First Name: <code>{$first_name}</code>\n👤 Last Name: <code>{$last_name}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " Create a Facebook account using the first name, last name and password above. Once the account is created, tap the <b>📤 Submit Facebook Cookies</b> button below."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    $msg_id = $msg_res['result']['message_id'] ?? '';
    
    setState($from_id, 'fb_cookies_task_active', $gen_fb_name . "|" . $admin_password . "|" . $msg_id);
    exit;
}

if (($text == '📤 ফেসবুক আইডি দিন' || $text == '📤 Submit Facebook ID') && $state == 'fb_cookies_task_active') {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🆔', '5231012545799666522') . " " . L($user_lang, " আপনার ফেসবুক অ্যাকাউন্টের <b>UID (ইউজার আইডি)</b> টি লিখে পাঠান:", "Enter the <b>UID (User ID)</b> of your Facebook account:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'fb_cookies_wait_uid', $temp);
    exit;
}

if ($state == 'fb_cookies_wait_uid' && $text != 'বাতিল' && $text != 'Cancel') {
    if (!ctype_digit($text) || strlen($text) < 9 || strlen($text) > 16) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " এটি একটি সঠিক ফেসবুক ইউআইডি (UID) নয়!\n\nসঠিক আইডিটি পুনরায় পাঠান:", "This is not a valid Facebook UID!\n\nPlease send the correct ID again:"), 'parse_mode' => 'HTML']);
        exit;
    }
    setState($from_id, 'fb_cookies_wait_data', $temp . "|" . $text); 
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🍪', '5229064374403998351') . " " . L($user_lang, " আপনার ফেসবুক অ্যাকাউন্টের <b>Cookies</b> টি এখানে পেস্ট করে পাঠান:", "Paste and send the <b>Cookies</b> of your Facebook account here:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'fb_cookies_wait_data' && $text != 'বাতিল' && $text != 'Cancel') {
    $cookies = trim($text);
    if (empty($cookies) || strlen($cookies) < 10) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " ফেসবুক কুকিজ সঠিক ফরম্যাটে দিন:", "Please provide the Facebook cookies in the correct format:"), 'parse_mode' => 'HTML']);
        exit;
    }
    
    setState($from_id, 'fb_cookies_wait_confirm', $temp . "|" . $cookies);
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang, " ফেসবুক কুকিজ ডাটা রেকর্ড করা হয়েছে।\n\nকাজটি ফাইনাল সাবমিট করতে নিচের <b>✅ কুকিজ সাবমিট সম্পূর্ণ করুন</b> বাটনে চাপুন।", "Facebook cookies data has been recorded.\n\nTap the <b>✅ Complete Cookies Submission</b> button below to finalize the submission."),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'কুকিজ সাবমিট সম্পূর্ণ করুন', 'Complete Cookies Submission'), 'success')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'fb_cookies_wait_confirm' && ($text == 'কুকিজ সাবমিট সম্পূর্ণ করুন' || $text == 'Complete Cookies Submission')) {
    $parts = explode('|', $temp);
    $name = $parts[0];
    $password = $parts[1];
    $msg_id = $parts[2];
    $fb_uid = $parts[3];
    $fb_cookies = $parts[4] ?? '';
    
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'facebook_cookies',
        'uid' => $fb_uid,
        'password' => $password,
        'cookies' => $fb_cookies,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $csv_file = fopen("data/facebook_cookies_master.csv", "a");
    fputcsv($csv_file, [$fb_uid, $password, $fb_cookies]);
    fclose($csv_file);
    
    seedSubmitTask($s, 'facebook_cookies', [
        'uid' => $fb_uid,
        'password' => $password,
        'cookies' => $fb_cookies
    ]);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($msg_id)) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('✅', '5206607081334906820') . " " . L($user_lang, " আপনার ফেসবুক কুকিজ কাজটি সফলভাবে সাবমিট হয়েছে এবং রিভিউতে পাঠানো হলো।", "Your Facebook Cookies task has been submitted successfully and sent for review."),
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

// ------------------- ৫. ইনস্টাগ্রাম কুকিজ কাজ -------------------
if ((strpos($text, 'Insta কুকিজ') !== false || strpos($text, 'ইনস্টা কুকিজ') !== false || strpos($text, 'ইনস্টাগ্রাম কুকিজ') !== false || strpos($text, 'Insta Cookies') !== false) && strpos($text, '(৳') !== false) {
    $legacy_video = getTaskVideo(getManagedTaskById('legacy_instagram_cookies'));
    if (!empty($legacy_video)) bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('🎥','5264919878082509254').' <b>কাজের ভিডিও:</b>','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>taskVideoButton($legacy_video,$user_lang)])]);

    if (($s['status_instagram_cookies'] ?? 'open') !== 'open') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে ইনস্টাগ্রাম কুকিজের কাজ বন্ধ আছে।", "Sorry, Instagram Cookies tasks are currently closed."), 'parse_mode' => 'HTML']);
        exit;
    }
    $reward_insta_cookies = number_format($s['reward_instagram_cookies'] ?? 8.00, 2);
    $gen_username = generateUsername();
    $admin_password = $s['admin_insta_cookies_password'] ?? 'Pass@instacookies';
    
    $menu = [
        [makeBtn(L($user_lang, '📤 ইনস্টাগ্রাম কুকিজ দিন', '📤 Submit Instagram Cookies'), 'success')],
        [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'danger')]
    ];
    
    $msg_res = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('📸', '5210956306952758910') . " " . L($user_lang,
            "<b>ইনস্টাগ্রাম কুকিজ তৈরির কাজ (৳{$reward_insta_cookies})</b>\n\n👤 Username: <code>{$gen_username}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " উপরের ইউজারনেম এবং পাসওয়ার্ড দিয়ে একটি ইনস্টাগ্রাম অ্যাকাউন্ট তৈরি করুন। অ্যাকাউন্ট তৈরি সম্পন্ন হলে নিচে <b>📤 ইনস্টাগ্রাম কুকিজ দিন</b> বাটনে ক্লিক করুন।",
            "<b>Instagram Cookies Creation Task (৳{$reward_insta_cookies})</b>\n\n👤 Username: <code>{$gen_username}</code>\n🔐 Password: <code>{$admin_password}</code>\n\n" . pe('📌', '5397782960512444700') . " Create an Instagram account using the username and password above. Once the account is created, tap the <b>📤 Submit Instagram Cookies</b> button below."
        ),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    $msg_id = $msg_res['result']['message_id'] ?? '';
    
    setState($from_id, 'insta_cookies_task_active', $gen_username . "|" . $admin_password . "|" . $msg_id);
    exit;
}

if (($text == '📤 ইনস্টাগ্রাম কুকিজ দিন' || $text == '📤 Submit Instagram Cookies') && $state == 'insta_cookies_task_active') {
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🍪', '5229064374403998351') . " " . L($user_lang, " আপনার ইনস্টাগ্রাম অ্যাকাউন্টের <b>Cookies</b> টি এখানে পেস্ট করে পাঠান:", "Paste and send the <b>Cookies</b> of your Instagram account here:"),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'বাতিল', 'Cancel'), 'danger')]], 'resize_keyboard' => true])
    ]);
    setState($from_id, 'insta_cookies_wait_data', $temp);
    exit;
}

if ($state == 'insta_cookies_wait_data' && $text != 'বাতিল' && $text != 'Cancel') {
    $cookies = trim($text);
    if (empty($cookies) || strlen($cookies) < 10) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " ইনস্টাগ্রাম কুকিজ সঠিক ফরম্যাটে দিন:", "Please provide the Instagram cookies in the correct format:"), 'parse_mode' => 'HTML']);
        exit;
    }
    
    setState($from_id, 'insta_cookies_wait_confirm', $temp . "|" . $cookies);
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang, " ইনস্টাগ্রাম কুকিজ ডাটা রেকর্ড করা হয়েছে।\n\nকাজটি ফাইনাল সাবমিট করতে নিচের <b>✅ কুকিজ সাবমিট সম্পূর্ণ করুন</b> বাটনে চাপুন।", "Instagram cookies data has been recorded.\n\nTap the <b>✅ Complete Cookies Submission</b> button below to finalize the submission."),
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, 'কুকিজ সাবমিট সম্পূর্ণ করুন', 'Complete Cookies Submission'), 'success')]], 'resize_keyboard' => true])
    ]);
    exit;
}

if ($state == 'insta_cookies_wait_confirm' && ($text == 'কুকিজ সাবমিট সম্পূর্ণ করুন' || $text == 'Complete Cookies Submission')) {
    $parts = explode('|', $temp);
    $username = $parts[0];
    $password = $parts[1];
    $msg_id = $parts[2];
    $insta_cookies = $parts[3] ?? '';
    
    $proofs = readDB('proofs.json');
    $proofs[] = [
        'id' => uniqid(),
        'user_id' => $from_id,
        'task_type' => 'instagram_cookies',
        'username' => $username,
        'password' => $password,
        'cookies' => $insta_cookies,
        'status' => 'pending'
    ];
    writeDB('proofs.json', $proofs);
    
    $csv_file = fopen("data/instagram_cookies_master.csv", "a");
    fputcsv($csv_file, [$username, $password, $insta_cookies]);
    fclose($csv_file);
    
    $users[$from_id]['review_tasks'] = ($users[$from_id]['review_tasks'] ?? 0) + 1;
    writeDB('users.json', $users);
    
    if (!empty($msg_id)) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('✅', '5206607081334906820') . " " . L($user_lang, " আপনার ইনস্টাগ্রাম কুকিজ কাজটি সফলভাবে সাবমিট হয়েছে এবং রিভিউতে পাঠানো হলো।", "Your Instagram Cookies task has been submitted successfully and sent for review."),
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

// ------------------- কাজের ভিডিও বাটন -------------------
if ($text == 'আমি নতুন') {
    $videos = $s['work_videos'] ?? [];
    if (empty($videos)) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, কাজ শেখার কোনো ভিডিও টিউটোরিয়াল set করা হয়নি।", "Sorry, no work tutorial videos have been set up yet."), 'parse_mode' => 'HTML']);
        exit;
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('🎬', '5264919878082509254') . " <b>" . L($user_lang, "কাজ শেখার ভিডিও টিউটোরিয়ালগুলো নিচে সরাসরি প্লে করার জন্য পাঠানো হলো:", "The work tutorial videos are being sent below to play directly:") . "</b> " . pe('⬇️', '5406745015365943482'),
        'parse_mode' => 'HTML'
    ]);
    
    foreach ($videos as $vid) {
        if (filter_var($vid, FILTER_VALIDATE_URL)) {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('🔗', '5271604874419647061') . " <a href='{$vid}'>" . L($user_lang, "টিউটোরিয়াল ভিডিও দেখতে এখানে ক্লিক করুন", "Click here to watch the tutorial video") . "</a>",
                'parse_mode' => 'HTML'
            ]);
        } else {
            bot('sendVideo', [
                'chat_id' => $chat_id,
                'video' => $vid,
                'caption' => pe('🎬', '5264919878082509254') . " <b>" . L($user_lang, "কাজ শেখার ভিডিও টিউটোরিয়াল", "Work Tutorial Video") . "</b>",
                'parse_mode' => 'HTML'
            ]);
        }
    }
    exit;
}

if ($text == 'ব্যালেন্স') {
    $bal = number_format($u['balance'], 2);
    $pending = number_format($u['pending_withdraw'], 2);
    $total_inc = number_format($u['total_income'], 2);
    $done = $u['completed_tasks'] ?? 0;
    $rev = $u['review_tasks'] ?? 0;
    $rejected = $u['rejected_tasks'] ?? 0; 
    $ti_word = L($user_lang, 'টি', '');
    
    $msg = pe('💵', '5409048419211682843') . " <b>" . L($user_lang, "আপনার ব্যালেন্স ওয়ালেট", "Your Balance Wallet") . "</b>\n"
         . "━━━━━━━━━━━━━━━━━\n"
         . pe('💰', '5233326571099534068') . " <b>" . L($user_lang, "ব্যালেন্স:", "Balance:") . "</b> {$bal} BDT\n"
         . pe('🔒', '5296369303661067030') . " <b>" . L($user_lang, "পেন্ডিং (উইথড্র):", "Pending (Withdraw):") . "</b> {$pending} BDT\n"
         . pe('💎', '5427168083074628963') . " <b>Total Income:</b> {$total_inc} BDT\n"
         . "━━━━━━━━━━━━━━━━━\n"
         . pe('✅', '5206607081334906820') . " <b>" . L($user_lang, "সম্পন্ন কাজ:", "Completed Tasks:") . "</b> {$done} {$ti_word}\n"
         . pe('⏳', '5386367538735104399') . " <b>" . L($user_lang, "রিভিউতে আছে:", "In Review:") . "</b> {$rev} {$ti_word}\n"
         . pe('❌', '5210952531676504517') . " <b>" . L($user_lang, "বাতিল কাজ:", "Rejected Tasks:") . "</b> {$rejected} {$ti_word}"; 
         
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'HTML']);
    exit;
}

if ($text == 'টাকা উত্তোলন') {
    $menu = [];
    $any_open = false;
    $min_word = L($user_lang, 'সর্বনিম্ন', 'Min');
    
    if (($s['status_wd_bkash'] ?? 'open') == 'open') {
        $min_bkash = $s['min_wd_bkash'];
        $charge_bkash = $s['bkash_charge'];
        $menu[] = [makeBtn("🔸 " . L($user_lang, 'বিকাশ', 'bKash') . " -> {$min_word}: {$min_bkash}৳(-{$charge_bkash})", 'primary')];
        $any_open = true;
    }
    if (($s['status_wd_nagad'] ?? 'open') == 'open') {
        $min_nagad = $s['min_wd_nagad'];
        $charge_nagad = $s['nagad_charge'];
        $menu[] = [makeBtn("🔸 " . L($user_lang, 'নগদ', 'Nagad') . " -> {$min_word}: {$min_nagad}৳(-{$charge_nagad})", 'primary')];
        $any_open = true;
    }
    if (($s['status_wd_rocket'] ?? 'open') == 'open') {
        $min_rocket = $s['min_wd_rocket'];
        $charge_rocket = $s['rocket_charge'];
        $menu[] = [makeBtn("🔸 " . L($user_lang, 'রকেট', 'Rocket') . " -> {$min_word}: {$min_rocket}৳(-{$charge_rocket})", 'primary')];
        $any_open = true;
    }
    
    $custom_methods_list = readMethods();
    foreach ($custom_methods_list as $cm) {
        if (($cm['status'] ?? 'open') == 'open') {
            $menu[] = [makeBtn(methodButtonLabel($cm, $user_lang), 'primary')];
            $any_open = true;
        }
    }
    
    if (!$any_open) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚠️', '5447644880824181073') . " " . L($user_lang, "দুঃখিত, বর্তমানে সকল পেমেন্ট উইথড্রাল অপশন সাময়িকভাবে বন্ধ রয়েছে।", "Sorry, all payment withdrawal options are temporarily closed right now."), 'parse_mode' => 'HTML']);
        exit;
    }
    
    $menu[] = [makeBtn(L($user_lang, '🔙 ফিরে যান', '🔙 Back'), 'primary')];
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('📥', '5406745015365943482') . " " . L($user_lang, "টাকা তোলার মাধ্যম সিলেক্ট করুন:", "Select a withdrawal method:"), 
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
    ]);
    exit;
}

if ((strpos($text, 'বিকাশ') !== false || strpos($text, 'bKash') !== false) && (strpos($text, 'সর্বনিম্ন') !== false || strpos($text, 'Min:') !== false)) {
    $min = $s['min_wd_bkash'];
    if ($u['balance'] < $min) {
        $bal_fmt = number_format($u['balance'], 2);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "<b>আপনার ব্যালেন্স পর্যাপ্ত নয়।</b>\nবিকাশে উইথড্র দিতে কমপক্ষে {$min} টাকা প্রয়োজন।\nআপনার বর্তমান ব্যালেন্স: {$bal_fmt} ৳", "<b>Your balance is not sufficient.</b>\nYou need at least {$min} Taka to withdraw via bKash.\nYour current balance: {$bal_fmt} ৳"), 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📱', '5231012545799666522') . " " . L($user_lang, "আপনার বিকাশ পার্সোনাল মোবাইল নাম্বারটি দিন:", "Enter your bKash personal mobile number:"), 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'bkash');
    exit;
}

if ((strpos($text, 'নগদ') !== false || strpos($text, 'Nagad') !== false) && (strpos($text, 'সর্বনিম্ন') !== false || strpos($text, 'Min:') !== false)) {
    $min = $s['min_wd_nagad'];
    if ($u['balance'] < $min) {
        $bal_fmt = number_format($u['balance'], 2);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "<b>আপনার ব্যালেন্স পর্যাপ্ত নয়।</b>\nনগদে উইথড্র দিতে কমপক্ষে {$min} টাকা প্রয়োজন।\nআপনার বর্তমান ব্যালেন্স: {$bal_fmt} ৳", "<b>Your balance is not sufficient.</b>\nYou need at least {$min} Taka to withdraw via Nagad.\nYour current balance: {$bal_fmt} ৳"), 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📱', '5231012545799666522') . " " . L($user_lang, "আপনার নগদ পার্সোনাল মোবাইল নাম্বারটি দিন:", "Enter your Nagad personal mobile number:"), 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'nagad');
    exit;
}

if ((strpos($text, 'রকেট') !== false || strpos($text, 'Rocket') !== false) && (strpos($text, 'সর্বনিম্ন') !== false || strpos($text, 'Min:') !== false)) {
    $min = $s['min_wd_rocket'];
    if ($u['balance'] < $min) {
        $bal_fmt = number_format($u['balance'], 2);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "<b>আপনার ব্যালেন্স পর্যাপ্ত নয়।</b>\nরকেটে উইথড্র দিতে কমপক্ষে {$min} টাকা প্রয়োজন।\nআপনার বর্তমান ব্যালেন্স: {$bal_fmt} ৳", "<b>Your balance is not sufficient.</b>\nYou need at least {$min} Taka to withdraw via Rocket.\nYour current balance: {$bal_fmt} ৳"), 'parse_mode' => 'HTML']);
        exit;
    }
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📱', '5231012545799666522') . " " . L($user_lang, "আপনার রকেট পার্সোনাল ১২ ডিজিটের মোবাইল নাম্বারটি দিন:", "Enter your Rocket personal 12-digit mobile number:"), 'parse_mode' => 'HTML']);
    setState($from_id, 'wait_wd_number', 'rocket');
    exit;
}

if (strpos($text, '🔸 ') === 0 && (strpos($text, 'সর্বনিম্ন') !== false || strpos($text, 'Min:') !== false)) {
    $custom_methods_check = readMethods();
    foreach ($custom_methods_check as $cm) {
        if (($cm['status'] ?? 'open') == 'open' && $text === methodButtonLabel($cm, $user_lang)) {
            $min = $cm['min'];
            if ($u['balance'] < $min) {
                $bal_fmt = number_format($u['balance'], 2);
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "<b>আপনার ব্যালেন্স পর্যাপ্ত নয়।</b>\n{$cm['name']}-এ উইথড্র দিতে কমপক্ষে {$min} টাকা প্রয়োজন।\nআপনার বর্তমান ব্যালেন্স: {$bal_fmt} ৳", "<b>Your balance is not sufficient.</b>\nYou need at least {$min} Taka to withdraw via {$cm['name']}.\nYour current balance: {$bal_fmt} ৳"), 'parse_mode' => 'HTML']);
                exit;
            }
            $field_label = $cm['field_label'] !== '' ? $cm['field_label'] : "{$cm['name']} " . L($user_lang, "অ্যাকাউন্ট/আইডি", "Account/ID");
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📱', '5231012545799666522') . " " . L($user_lang, "আপনার {$field_label} দিন:", "Enter your {$field_label}:"), 'parse_mode' => 'HTML']);
            setState($from_id, 'wait_wd_number', 'custom:' . $cm['id']);
            exit;
        }
    }
}

if ($state == 'wait_wd_number') {
    $method = $temp; 
    setState($from_id, 'wait_wd_amount', $method . "|" . $text);
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " " . L($user_lang, "উত্তোলনের পরিমাণ লিখুন (BDT):", "Enter the withdrawal amount (BDT):"), 'parse_mode' => 'HTML']);
    exit;
}

if ($state == 'wait_wd_amount' && is_numeric($text)) {
    $parts = explode('|', $temp);
    $method_key = $parts[0];
    $account = $parts[1] ?? '';
    $amount = floatval($text);

    $is_custom = (strpos($method_key, 'custom:') === 0);
    $charge = 0;
    $method_display = $method_key;

    if ($is_custom) {
        $custom_id = substr($method_key, 7);
        $custom_methods_now = readMethods();
        $cm = findMethodById($custom_methods_now, $custom_id);

        if ($cm === null || ($cm['status'] ?? 'open') !== 'open') {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "দুঃখিত, এই মেথডটি বর্তমানে উপলব্ধ নেই।", "Sorry, this method is not available."), 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
            setState($from_id, "");
            exit;
        }
        $min = $cm['min'];
        $charge = $cm['charge'];
        $method_display = $cm['name'];
    } else {
        $min = $s['min_wd_' . $method_key] ?? 0;
        $charge = $s[$method_key . '_charge'] ?? 0;
    }

    if ($amount < $min || $amount > $u['balance']) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "লিমিট বা ব্যালেন্স সমস্যা। পুনরায় চেষ্টা করুন।", "There is a limit or balance issue. Please try again."), 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
        setState($from_id, "");
        exit;
    }

    $net_amount = max(0, $amount - $charge);

    $users[$from_id]['balance'] -= $amount;
    $users[$from_id]['pending_withdraw'] += $amount;
    $write_ok = writeDB('users.json', $users);

    if (!$write_ok) {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "সার্ভারে সমস্যা হয়েছে, আবার চেষ্টা করুন।", "Server error, please try again."), 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
        setState($from_id, "");
        exit;
    }

    $wd_id = uniqid();
    $withdraws = readDB('withdraws.json');
    $withdraws[] = [
        'id' => $wd_id,
        'user_id' => $from_id,
        'username' => $users[$from_id]['username'] ?? '',
        'method' => $method_display,
        'account' => $account,
        'amount' => $amount,
        'charge' => $charge,
        'net_amount' => $net_amount,
        'request_time' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];
    $write_ok2 = writeDB('withdraws.json', $withdraws);

    if (!$write_ok2) {
        $users[$from_id]['balance'] += $amount;
        $users[$from_id]['pending_withdraw'] -= $amount;
        writeDB('users.json', $users);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, "সার্ভারে সমস্যা, ব্যালেন্স অপরিবর্তিত। আবার চেষ্টা করুন।", "Server error, balance unchanged."), 'parse_mode' => 'HTML', 'reply_markup' => getMainMenu($from_id)]);
        setState($from_id, "");
        exit;
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('⏳', '5386367538735104399') . " " . L($user_lang, "উইথড্রাল রিকোয়েস্টটি সফলভাবে জমা হয়েছে।", "Your withdrawal request has been submitted successfully."), 
        'parse_mode' => 'HTML',
        'reply_markup' => getMainMenu($from_id)
    ]);
    setState($from_id, "");
    exit;
}

if ($text == 'My Referrals') {
    $total_ref = $u['total_refs'] ?? 0;
    $ref_inc = number_format($u['ref_income'] ?? 0, 2);
    $ref_comm = $s['ref_commission'] ?? 5;
    
    $msg = pe('🚀', '5456140674028019486') . " <b>My Referrals Portal</b>\n"
         . pe('👤', '5460795800101594035') . " <b>Total Refer:</b> {$total_ref}\n"
         . pe('💲', '5409048419211682843') . " <b>Total Refer Income:</b> {$ref_inc} BDT\n"
         . pe('🔗', '5271604874419647061') . " <b>" . L($user_lang, "আপনার রেফার লিংক:", "Your referral link:") . "</b>\n"
         . "https://t.me/{$bot_username}?start={$from_id}\n\n"
         . pe('ℹ️', '5334544901428229844') . " " . L($user_lang, "আপনি আপনার প্রতি রেফারেল থেকে আয়ের {$ref_comm}% কমিশন পাবেন।", "You will earn {$ref_comm}% commission from each referral's earnings.");
         
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => $msg, 'parse_mode' => 'HTML']);
    exit;
}

if ($text == 'সাপোর্ট') {
    $inline_kb = [
        [['text' => "✔️ 👨\u{200D}✈️ " . L($user_lang, 'অ্যাডমিন সাপোর্ট', 'Admin Support') . ' ↗️', 'url' => $s['support_id']]],
        [['text' => '✈️ 📢 ' . L($user_lang, 'অফিশিয়াল চ্যানেল', 'Official Channel') . ' ↗️', 'url' => $s['official_channel']]]
    ];
    bot('sendMessage', [
        'chat_id' => $chat_id, 
        'text' => pe('💬', '5443038326535759644') . " <b>" . L($user_lang, "গ্রাহক সেবা কেন্দ্র", "Customer Support Center") . "</b>\n\n" . L($user_lang, "আপনার যেকোনো সমস্যা বা জিজ্ঞাসার জন্য আমাদের সাপোর্ট সেন্টারে যোগাযোগ করুন।", "Contact our support center for any issues or questions you may have."), 
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
    ]);
    exit;
}

// === অংশ ২/৩ শেষ ===
// পরের মেসেজে অংশ ৩/৩ আসবে (এডমিন প্যানেল + Callback + CSV)

# ==================== PART SEPARATOR ====================

// === অংশ ৩/৩ শুরু ===

// ==========================================
// 🛡️ অ্যাডমিন প্যানেল লজিক
// ==========================================
if (strval($from_id) === strval(ADMIN_ID)) {

    if ($text == 'Admin Dashboard' || $text == '⚙️ এডমিন প্যানেল' || ($text == '🔙 এডমিন মেনু' || $text == '🔙 Admin Menu')) {
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚙️', '5341715473882955310') . " " . L($user_lang, " <b>এডমিন কন্ট্রোল প্যানেল</b>", "<b>Admin Control Panel</b>"),
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminMenu()
        ]);
        exit;
    }

    if ($text == 'পরিসংখ্যান') {
        $total_users = count($users);
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📊', '5231200819986047254') . " " . L($user_lang, " <b>বটের পরিসংখ্যান:</b>\n\n👥 মোট রেজিস্টার্ড ইউজার সংখ্যা: <b>{$total_users}</b> জন", "<b>Bot Statistics:</b>\n\n👥 Total registered users: <b>{$total_users}</b>"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }

    if ($text == 'ইউজার সার্চ') {
        setState($from_id, 'wait_search_user_id');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🔍', '5231012545799666522') . " " . L($user_lang, " ইউজার ডেটা দেখতে সঠিক <b>Telegram ID</b> অথবা <b>Username</b> পাঠান:", "Send the correct <b>Telegram ID</b> or <b>Username</b> to view user data:"),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 এডমিন মেনু', '🔙 Admin Menu'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }

    if ($state == 'wait_search_user_id' && $text != '🔙 এডমিন মেনু' && $text != '🔙 Admin Menu') {
        $search_query = trim($text);
        $search_query_clean = ltrim($search_query, '@');
        $found_uid = null;
        
        if (isset($users[$search_query])) {
            $found_uid = $search_query;
        } else {
            foreach ($users as $uid => $target_user) {
                if (isset($target_user['username']) && strcasecmp(trim($target_user['username']), $search_query_clean) === 0) {
                    $found_uid = $uid;
                    break;
                }
            }
        }
        
        if ($found_uid !== null) {
            $target_user = $users[$found_uid];
            $msg = pe('👤', '5460795800101594035') . " " . L($user_lang,
                "<b>ইউজার তথ্য (ID: {$found_uid})</b>\n\n"
                     . "🏷️ নাম: {$target_user['name']}\n"
                     . "💰 ব্যালেন্স: {$target_user['balance']} BDT\n"
                     . "⏳ পেন্ডিং উইথড্র: {$target_user['pending_withdraw']} BDT\n"
                     . "✅ সম্পন্ন কাজ: {$target_user['completed_tasks']} টি\n"
                     . "⏳ রিভিউ কাজ: {$target_user['review_tasks']} টি",
                "<b>User Info (ID: {$found_uid})</b>\n\n"
                     . "🏷️ Name: {$target_user['name']}\n"
                     . "💰 Balance: {$target_user['balance']} BDT\n"
                     . "⏳ Pending Withdraw: {$target_user['pending_withdraw']} BDT\n"
                     . "✅ Completed Tasks: {$target_user['completed_tasks']}\n"
                     . "⏳ Review Tasks: {$target_user['review_tasks']}"
            );
            
            $inline_kb = [
                [
                    ['text' => L($user_lang, '➕ ব্যালেন্স বাড়ান', '➕ Add Balance'), 'callback_data' => "bal_add_{$found_uid}"],
                    ['text' => L($user_lang, '➖ ব্যালেন্স কমান', '➖ Subtract Balance'), 'callback_data' => "bal_sub_{$found_uid}"]
                ]
            ];
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
            ]);
            setState($from_id, "");
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " কোনো ইউজার পাওয়া যায়নি।", "No user found."), 'parse_mode' => 'HTML']);
        }
        exit;
    }

    if ($text == 'বটের সেটিংস' || ($text == '🔙 বটের সেটিংস' || $text == '🔙 Bot Settings')) {
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚙️', '5341715473882955310') . " " . L($user_lang, " <b>বটের গ্লোবাল সেটিংস</b>", "<b>Bot Global Settings</b>"),
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminSettingsMenu()
        ]);
        exit;
    }

    // ============================================================
    // 🆕 কাজ ম্যানেজমেন্ট হ্যান্ডলার
    // ============================================================
    if ($text == '🆕 কাজ ম্যানেজমেন্ট' || $text == '🆕 Task Management') {
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🆕', '5397916757333654639') . " " . L($user_lang, " <b>কাজ ম্যানেজমেন্ট প্যানেল</b>\n\nএখান থেকে যেকোনো কাজ যোগ, এডিট, ডিলিট বা চালু/বন্ধ করতে পারবেন।", "<b>Task Management Panel</b>\n\nAdd, edit, delete or toggle any task here."),
            'parse_mode' => 'HTML',
            'reply_markup' => getTaskManageMenu()
        ]);
        exit;
    }
    
    if ($text == '➕ নতুন কাজ যোগ করুন' || $text == '➕ Add New Task') {
        setState($from_id, 'wait_new_task_name');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🏷️', '5253742260054409879') . " " . L($user_lang, " নতুন কাজের নাম লিখুন (যেমন: Pc clone 1000x):", "Enter the new task name (e.g., Pc clone 1000x):"),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    
    if ($text == '📋 সব কাজ দেখুন' || $text == '📋 View All Tasks') {
        showAdminTaskList($chat_id);
        exit;
    }
    
    // ধাপ ১: নাম
    if ($state == 'wait_new_task_name' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $task_name = trim($text);
        if ($task_name === '' || mb_strlen($task_name) > 50) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " সঠিক নাম দিন (১-৫০ ক্যারেক্টার)।", "Provide a valid name (1-50 chars)."), 'parse_mode' => 'HTML']);
            exit;
        }
        foreach (readTasks() as $et) {
            if (strcasecmp(trim($et['name']), $task_name) === 0) {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " এই নামে ইতিমধ্যে একটি কাজ আছে।", "A task with this name already exists."), 'parse_mode' => 'HTML']);
                exit;
            }
        }
        setState($from_id, 'wait_new_task_emoji', json_encode(['name' => $task_name]));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('😀', '5253742260054409879') . " " . L($user_lang, " একটি Emoji পাঠান (যেমন: 🖥️, 📸, 📧):", "Send an Emoji (e.g., 🖥️, 📸, 📧):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // ধাপ ২: Emoji
    if ($state == 'wait_new_task_emoji' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $emoji = trim($text);
        if (mb_strlen($emoji) > 5 || $emoji === '') {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " " . L($user_lang, " একটি Emoji পাঠান।", "Send one Emoji."), 'parse_mode' => 'HTML']);
            exit;
        }
        $step = json_decode($temp, true);
        $step['emoji'] = $emoji;
        setState($from_id, 'wait_new_task_reward', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('💰', '5233326571099534068') . " " . L($user_lang, " কাজের রিওয়ার্ড (৳) লিখুন:", "Enter the task reward (৳):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // ধাপ ৩: রিওয়ার্ড
    if ($state == 'wait_new_task_reward' && is_numeric($text)) {
        $step = json_decode($temp, true);
        $step['reward'] = floatval($text);
        setState($from_id, 'wait_new_task_password', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang, " কাজের কমন পাসওয়ার্ড লিখুন (ইউজারকে দেখানো হবে):", "Enter the common password (shown to user):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // ধাপ ৪: পাসওয়ার্ড
    if ($state == 'wait_new_task_password' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $step = json_decode($temp, true);
        $step['password'] = trim($text);
        setState($from_id, 'wait_new_task_fields', json_encode($step));
        
        $all_fields = ['uid' => 'UID', '2fa' => '2FA', 'cookies' => 'Cookies', 'username' => 'Username(auto)', 'email' => 'Email(auto)', 'fb_name' => 'FB Name(auto)', 'password' => 'Password(auto)'];
        $kb = [];
        $row = [];
        foreach ($all_fields as $k => $lbl) {
            $row[] = ['text' => '⬜ ' . $lbl, 'callback_data' => 'ntf_toggle_' . $k];
            if (count($row) == 2) { $kb[] = $row; $row = []; }
        }
        if (!empty($row)) $kb[] = $row;
        $kb[] = [['text' => '✅ Done', 'callback_data' => 'ntf_done']];
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📝', '5253742260054409879') . " " . L($user_lang,
                "<b>ইনপুট ফিল্ড সিলেক্ট করুন।</b>\n\nইউজারের কাছ থেকে যে ফিল্ডগুলো নিতে চান সেগুলো টগল করুন, তারপর <b>Done</b> চাপুন।",
                "<b>Select input fields.</b>\n\nToggle the fields you want to collect from the user, then tap <b>Done</b>."
            ),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $kb])
        ]);
        exit;
    }
    
    // UID Min
    if ($state == 'wait_new_task_uid_min' && is_numeric($text)) {
        $step = json_decode($temp, true);
        $step['uid_min_length'] = intval($text);
        setState($from_id, 'wait_new_task_uid_max', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🆔', '5231012545799666522') . " " . L($user_lang, " UID এর সর্বোচ্চ ডিজিট সংখ্যা লিখুন (যেমন: 16):", "Enter max digits for UID (e.g., 16):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // UID Max
    if ($state == 'wait_new_task_uid_max' && is_numeric($text)) {
        $step = json_decode($temp, true);
        $step['uid_max_length'] = intval($text);
        setState($from_id, 'wait_new_task_instruction', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📌', '5397782960512444700') . " " . L($user_lang, " ইউজারকে দেখানোর জন্য একটি নির্দেশনা লিখুন।\n\nউদাহরণ: <i>বট থেকে একটা বট নাম্বার নিয়ে Pc clone আইডি খুলুন এবং UID দিন</i>\n\nএড়াতে চাইলে <code>skip</code> লিখুন:", "Enter an instruction for users.\n\nType <code>skip</code> to skip:"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // নির্দেশনা
    if ($state == 'wait_new_task_instruction' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $step = json_decode($temp, true);
        $step['instruction'] = (strtolower(trim($text)) === 'skip') ? '' : trim($text);
        setState($from_id, 'wait_new_task_sheeturl', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🔗', '5271604874419647061') . " " . L($user_lang, " Google Apps Script URL দিন (বা <code>skip</code>):", "Enter Google Apps Script URL (or <code>skip</code>):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // URL
    if ($state == 'wait_new_task_sheeturl' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $step = json_decode($temp, true);
        $step['sheet_url'] = (strtolower(trim($text)) === 'skip') ? '' : trim($text);
        setState($from_id, 'wait_new_task_sheetname', json_encode($step));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📄', '5253742260054409879') . " " . L($user_lang, " Google Sheet নাম লিখুন (বা <code>skip</code>):", "Enter Google Sheet name (or <code>skip</code>):"),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    // Sheet নাম — ফাইনাল সেভ
    if ($state == 'wait_new_task_sheetname' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $step = json_decode($temp, true);
        $step['sheet_name'] = (strtolower(trim($text)) === 'skip') ? $step['name'] : trim($text);
        
        $new_task = [
            'id' => genTaskId($step['name']),
            'name' => $step['name'],
            'emoji' => $step['emoji'],
            'reward' => $step['reward'],
            'password' => $step['password'],
            'status' => 'open',
            'input_fields' => $step['input_fields'],
            'field_button_labels' => [
                'uid' => '🆔 UID সাবমিট করুন',
                '2fa' => '🔐 2FA সাবমিট করুন',
                'cookies' => '🍪 Cookies সাবমিট করুন'
            ],
            'uid_min_length' => $step['uid_min_length'] ?? 10,
            'uid_max_length' => $step['uid_max_length'] ?? 16,
            'instruction' => $step['instruction'] ?? '',
            'sheet_url' => $step['sheet_url'] ?? '',
            'sheet_name' => $step['sheet_name'] ?? $step['name'],
            'video' => '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $all = readTasks();
        $all[] = $new_task;
        $ok = writeTasks($all);
        if ($ok) ensureTaskCsv($new_task);
        
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('✅', '5206607081334906820') . " " . L($user_lang,
                "নতুন কাজ <b>{$new_task['name']}</b> সফলভাবে যোগ করা হয়েছে!\n\n💰 রিওয়ার্ড: ৳{$new_task['reward']}\n🔐 পাসওয়ার্ড: <code>{$new_task['password']}</code>",
                "New task <b>{$new_task['name']}</b> added successfully!\n\n💰 Reward: ৳{$new_task['reward']}\n🔐 Password: <code>{$new_task['password']}</code>"
            ),
            'parse_mode' => 'HTML',
            'reply_markup' => getTaskManageMenu()
        ]);
        exit;
    }
    
    // প্রতি কাজের আলাদা ভিডিও সেট/আপডেট
    if ($state == 'wait_old_task_video' || $state == 'wait_new_task_video') {
        $info = json_decode($temp, true); $id = $info['id'] ?? ''; $source = $info['source'] ?? 'custom';
        $video = trim($text);
        if (strtolower($video) === 'skip' || $video === '-') $video = '';
        if ($video !== '' && !preg_match('~^https?://~i', $video)) {
            bot('sendMessage',['chat_id'=>$chat_id,'text'=>'❌ ভিডিও লিংক অবশ্যই http:// অথবা https:// দিয়ে শুরু হতে হবে।']); exit;
        }
        $task = getManagedTaskById($id); if ($task === null) { setState($from_id,''); exit; }
        setTaskVideo($task, $video); setState($from_id,'');
        $fresh = getManagedTaskById($id);
        $view = ($source === 'legacy') ? legacyTaskDetailView($fresh,$user_lang) : taskDetailView($fresh,$user_lang);
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('✅','5206607081334906820').' '.($video ? 'এই কাজের ভিডিও সেট হয়েছে।' : 'এই কাজের ভিডিও মুছে দেওয়া হয়েছে।'),'parse_mode'=>'HTML','reply_markup'=>$view['reply_markup']]); exit;
    }

    // পুরোনো ৫টি কাজের Edit value
    if ($state == 'wait_old_task_edit' && $text != '🔙 কাজ ম্যানেজমেন্ট') {
        $ei=json_decode($temp,true); $defs=getLegacyTaskDefinitions(); $id=$ei['id']??''; $field=$ei['field']??'';
        if (!isset($defs[$id])) { setState($from_id,''); exit; }
        if ($field==='reward' && !is_numeric($text)) { bot('sendMessage',['chat_id'=>$chat_id,'text'=>'❌ সঠিক রিওয়ার্ড লিখুন।']); exit; }
        if ($field==='name' && trim($text)==='') { bot('sendMessage',['chat_id'=>$chat_id,'text'=>'❌ নাম খালি রাখা যাবে না।']); exit; }
        if ($field==='reward') $s[$defs[$id]['reward_key']]=floatval($text);
        elseif ($field==='password') $s[$defs[$id]['password_key']]=trim($text);
        elseif ($field==='name') $s['custom_name_'.$id]=trim($text);
        writeDB('settings.json',$s); setState($from_id,'');
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>'✅ কাজের তথ্য আপডেট হয়েছে।','parse_mode'=>'HTML','reply_markup'=>getTaskManageMenu()]); exit;
    }

    // ইনপুট বাটনের নাম এডিট
    if ($state == 'wait_edit_task_button' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $edit_info = json_decode($temp, true);
        $all_tasks = readTasks();
        $idx = findTaskIndex($all_tasks, $edit_info['id'] ?? '');
        $field = $edit_info['field'] ?? '';
        $new_label = trim($text);
        if ($idx === -1 || !in_array($field, ['uid','2fa','cookies'], true) || $new_label === '') {
            bot('sendMessage',['chat_id'=>$chat_id,'text'=>'❌ সঠিক বাটনের নাম দিন।','parse_mode'=>'HTML']);
            exit;
        }
        if (!isset($all_tasks[$idx]['field_button_labels']) || !is_array($all_tasks[$idx]['field_button_labels'])) {
            $all_tasks[$idx]['field_button_labels'] = [];
        }
        $all_tasks[$idx]['field_button_labels'][$field] = mb_substr($new_label, 0, 60);
        writeTasks($all_tasks);
        setState($from_id, '');
        $view = taskDetailView($all_tasks[$idx], $user_lang);
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>'✅ '.$field.' বাটনের নাম আপডেট হয়েছে।','parse_mode'=>'HTML','reply_markup'=>$view['reply_markup']]);
        exit;
    }

    // এডিট ভ্যালু
    if ($state == 'wait_edit_task_value'
# This intentionally malformed marker will be fixed below.
 && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $edit_info = json_decode($temp, true);
        if (!is_array($edit_info) || empty($edit_info['id']) || empty($edit_info['field'])) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সমস্যা হয়েছে, আবার চেষ্টা করুন।", 'parse_mode' => 'HTML', 'reply_markup' => getTaskManageMenu()]);
            setState($from_id, "");
            exit;
        }
        $all_tasks = readTasks();
        $idx = findTaskIndex($all_tasks, $edit_info['id']);
        if ($idx === -1) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " কাজটি খুঁজে পাওয়া যায়নি।", 'parse_mode' => 'HTML', 'reply_markup' => getTaskManageMenu()]);
            setState($from_id, "");
            exit;
        }
        $field = $edit_info['field'];
        $new_val = trim($text);
        
        if ($field === 'reward') {
            if (!is_numeric($new_val) || floatval($new_val) < 0) {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক সংখ্যা দিন।", 'parse_mode' => 'HTML']);
                exit;
            }
            $all_tasks[$idx]['reward'] = floatval($new_val);
        } elseif ($field === 'uidlimit') {
            if (!preg_match('/^(\d+)\s*-\s*(\d+)$/', $new_val, $m)) {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক ফরম্যাট: <code>10-16</code>", 'parse_mode' => 'HTML']);
                exit;
            }
            $all_tasks[$idx]['uid_min_length'] = intval($m[1]);
            $all_tasks[$idx]['uid_max_length'] = intval($m[2]);
        } elseif ($field === 'name') {
            foreach ($all_tasks as $chk_idx => $et) {
                if ($chk_idx != $idx && strcasecmp(trim($et['name']), $new_val) === 0) {
                    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " এই নামে আরেকটি কাজ আছে।", 'parse_mode' => 'HTML']);
                    exit;
                }
            }
            $all_tasks[$idx]['name'] = $new_val;
        } elseif ($field === 'emoji') {
            if (mb_strlen($new_val) > 5 || $new_val === '') {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " একটি Emoji পাঠান।", 'parse_mode' => 'HTML']);
                exit;
            }
            $all_tasks[$idx]['emoji'] = $new_val;
        } elseif ($field === 'password') {
            $all_tasks[$idx]['password'] = $new_val;
        } elseif ($field === 'instruction') {
            $all_tasks[$idx]['instruction'] = $new_val;
        }
        
        writeTasks($all_tasks);
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('✅', '5206607081334906820') . " কাজ আপডেট সম্পন্ন!",
            'parse_mode' => 'HTML',
            'reply_markup' => getTaskManageMenu()
        ]);
        setState($from_id, "");
        exit;
    }

    // পেমেন্ট টগল
    if ($text == 'পেমেন্ট অন/অফ' || $text == 'Payment On/Off') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚙️', '5341715473882955310') . " " . L($user_lang, " <b>পেমেন্ট উইথড্র অন/অফ প্যানেল:</b>", "<b>Payment Withdrawal On/Off Panel:</b>"),
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminPaymentToggleMenu($s)
        ]);
        exit;
    }
    
    if ($text == '🟢 বিকাশ উইথড্র চালু' || $text == '🔴 বিকাশ উইথড্র বন্ধ' || $text == '🟢 bKash Withdraw On' || $text == '🔴 bKash Withdraw Off') {
        $s['status_wd_bkash'] = ($s['status_wd_bkash'] ?? 'open') == 'open' ? 'closed' : 'open';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " বিকাশ উইথড্রাল স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminPaymentToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 নগদ উইথড্র চালু' || $text == '🔴 নগদ উইথড্র বন্ধ' || $text == '🟢 Nagad Withdraw On' || $text == '🔴 Nagad Withdraw Off') {
        $s['status_wd_nagad'] = ($s['status_wd_nagad'] ?? 'open') == 'open' ? 'closed' : 'open';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " নগদ উইথড্রাল স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminPaymentToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 রকেট উইথড্র চালু' || $text == '🔴 রকেট উইথড্র বন্ধ' || $text == '🟢 Rocket Withdraw On' || $text == '🔴 Rocket Withdraw Off') {
        $s['status_wd_rocket'] = ($s['status_wd_rocket'] ?? 'open') == 'open' ? 'closed' : 'open';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " রকেট উইথড্রাল স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminPaymentToggleMenu($s)]);
        exit;
    }

    // কাজ টগল
    if ($text == 'কাজ চালু/বন্ধ' || $text == 'Task On/Off') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('⚙️', '5341715473882955310') . " " . L($user_lang, " <b>কাজ চালু অথবা বন্ধ করার প্যানেল:</b>", "<b>Task On/Off Panel:</b>"),
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminTaskToggleMenu($s)
        ]);
        exit;
    }
    
    if ($text == '🟢 ইনস্টা চালু করুন' || $text == '🔴 ইনস্টা বন্ধ করুন' || $text == '🟢 Turn On Instagram' || $text == '🔴 Turn Off Instagram') {
        $s['status_instagram'] = ($text == '🟢 ইনস্টা চালু করুন' || $text == '🟢 Turn On Instagram') ? 'open' : 'closed';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টাগ্রাম স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminTaskToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 জিমেইল চালু করুন' || $text == '🔴 জিমেইল বন্ধ করুন' || $text == '🟢 Turn On Gmail' || $text == '🔴 Turn Off Gmail') {
        $s['status_gmail'] = ($text == '🟢 জিমেইল চালু করুন' || $text == '🟢 Turn On Gmail') ? 'open' : 'closed';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " জিমেইল স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminTaskToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 ফেসবুক চালু করুন' || $text == '🔴 ফেসবুক বন্ধ করুন' || $text == '🟢 Turn On Facebook' || $text == '🔴 Turn Off Facebook') {
        $s['status_facebook'] = ($text == '🟢 ফেসবুক চালু করুন' || $text == '🟢 Turn On Facebook') ? 'open' : 'closed';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminTaskToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 কুকিজ চালু করুন' || $text == '🔴 কুকিজ বন্ধ করুন' || $text == '🟢 Turn On Cookies' || $text == '🔴 Turn Off Cookies') {
        $s['status_facebook_cookies'] = ($text == '🟢 কুকিজ চালু করুন' || $text == '🟢 Turn On Cookies') ? 'open' : 'closed';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " কুকিজ স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminTaskToggleMenu($s)]);
        exit;
    }
    if ($text == '🟢 ইনস্টা কুকিজ চালু করুন' || $text == '🔴 ইনস্টা কুকিজ বন্ধ করুন' || $text == '🟢 Turn On Insta Cookies' || $text == '🔴 Turn Off Insta Cookies') {
        $s['status_instagram_cookies'] = ($text == '🟢 ইনস্টা কুকিজ চালু করুন' || $text == '🟢 Turn On Insta Cookies') ? 'open' : 'closed';
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টা কুকিজ স্ট্যাটাস আপডেট।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminTaskToggleMenu($s)]);
        exit;
    }

    // পাসওয়ার্ড সেটিংস
    if ($text == 'পাসওয়ার্ড সেটিংস') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🔐', '5296369303661067030') . " " . L($user_lang, " <b>পাসওয়ার্ড কন্ট্রোল প্যানেল</b>", "<b>Password Control Panel</b>"),
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminPasswordSettingsMenu()
        ]);
        exit;
    }
    if ($text == 'ইনস্টা পাসওয়ার্ড' || $text == 'Insta Password') {
        setState($from_id, 'set_pass_insta');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔐', '5296369303661067030') . " ইনস্টার কমন পাসওয়ার্ড দিন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_pass_insta' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['admin_insta_password'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টা পাসওয়ার্ড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'জিমেইল পাসওয়ার্ড' || $text == 'Gmail Password') {
        setState($from_id, 'set_pass_gmail');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔐', '5296369303661067030') . " জিমেইলের কমন পাসওয়ার্ড দিন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_pass_gmail' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['admin_gmail_password'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " জিমেইল পাসওয়ার্ড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'ফেসবুক পাসওয়ার্ড' || $text == 'Facebook Password') {
        setState($from_id, 'set_pass_fb');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔐', '5296369303661067030') . " ফেসবুকের কমন পাসওয়ার্ড দিন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_pass_fb' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['admin_fb_password'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক পাসওয়ার্ড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'কুকিজ পাসওয়ার্ড' || $text == 'Cookies Password') {
        setState($from_id, 'set_pass_fb_cookies');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔐', '5296369303661067030') . " ফেসবুক কুকিজের কমন পাসওয়ার্ড দিন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_pass_fb_cookies' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['admin_fb_cookies_password'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক কুকিজ পাসওয়ার্ড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ পাসওয়ার্ড' || $text == 'Insta Cookies Password') {
        setState($from_id, 'set_pass_insta_cookies');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔐', '5296369303661067030') . " ইনস্টা কুকিজের কমন পাসওয়ার্ড দিন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_pass_insta_cookies' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['admin_insta_cookies_password'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টা কুকিজ পাসওয়ার্ড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // ভিডিও
    if ($text == '🎥 কাজের ভিডিও সেট') {
        $videos = $s['work_videos'] ?? [];
        $v_list = "";
        $i = 1;
        foreach ($videos as $v) {
            $v_list .= "{$i}. <code>{$v}</code>\n";
            $i++;
        }
        if (empty($v_list)) { $v_list = "কোনো ভিডিও যোগ করা নেই।\n"; }
        
        $menu = [
            [makeBtn(L($user_lang, 'ভিডিও যোগ করুন', 'Add Video'), 'success'), makeBtn(L($user_lang, 'সব ভিডিও মুছুন', 'Delete All Videos'), 'danger')],
            [makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'primary')]
        ];
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🎬', '5264919878082509254') . " " . L($user_lang, " <b>Current টিউটোরিয়াল ভিডিও তালিকা:</b>\n\n{$v_list}\nনিচের বাটনগুলো ব্যবহার করে ভিডিও যোগ বা ডিলিট করুন:", "<b>Current Tutorial Video List:</b>\n\n{$v_list}\nUse the buttons below to add or delete videos:"),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($text == 'ভিডিও যোগ করুন' || $text == 'Add Video') {
        setState($from_id, 'wait_add_video');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🔗', '5271604874419647061') . " " . L($user_lang, " ভিডিওর লিঙ্ক (Youtube) অথবা সরাসরি ভিডিও ফাইল আপলোড করুন:", "Send the video link (Youtube) or upload a video file directly:"),
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'wait_add_video' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings' && !empty($text)) {
        $s['work_videos'][] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " নতুন ভিডিও লিংক যোগ হয়েছে!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'সব ভিডিও মুছুন' || $text == 'Delete All Videos') {
        $s['work_videos'] = [];
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " সমস্ত ভিডিও মুছে ফেলা হয়েছে!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        exit;
    }

    // রেফার কমিশন
    if ($text == 'রেফার কমিশন সেট') {
        setState($from_id, 'set_ref_comm_pct');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('👥', '5460795800101594035') . " প্রতি রেফারেল থেকে কত % কমিশন (যেমন: 5):",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'set_ref_comm_pct' && is_numeric($text)) {
        $s['ref_commission'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " রেফার কমিশন আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // লিমিট ও চার্জ
    if ($text == 'লিমিট ও চার্জ সেট') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📱', '5231012545799666522') . " <b>উইথড্র লিমিট ও চার্জ সেটিংস</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminLimitSettingsMenu()
        ]);
        exit;
    }
    if ($text == 'বিকাশ লিমিট' || $text == 'bKash Limit') {
        setState($from_id, 'set_limit_bkash');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " বিকাশ সর্বনিম্ন উইথড্র লিমিট (BDT):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_bkash' && is_numeric($text)) {
        $s['min_wd_bkash'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " বিকাশ লিমিট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'বিকাশ চার্জ' || $text == 'bKash Charge') {
        setState($from_id, 'set_charge_bkash');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " বিকাশ চার্জ (৳):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_bkash' && is_numeric($text)) {
        $s['bkash_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " বিকাশ চার্জ আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'নগদ লিমিট' || $text == 'Nagad Limit') {
        setState($from_id, 'set_limit_nagad');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " নগদ সর্বনিম্ন উইথড্র লিমিট (BDT):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_nagad' && is_numeric($text)) {
        $s['min_wd_nagad'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " নগদ লিমিট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'নগদ চার্জ' || $text == 'Nagad Charge') {
        setState($from_id, 'set_charge_nagad');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " নগদ চার্জ (৳):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_nagad' && is_numeric($text)) {
        $s['nagad_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " নগদ চার্জ আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'রকেট লিমিট' || $text == 'Rocket Limit') {
        setState($from_id, 'set_limit_rocket');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " রকেট সর্বনিম্ন উইথড্র লিমিট (BDT):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_limit_rocket' && is_numeric($text)) {
        $s['min_wd_rocket'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " রকেট লিমিট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'রকেট চার্জ' || $text == 'Rocket Charge') {
        setState($from_id, 'set_charge_rocket');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " রকেট চার্জ (৳):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_charge_rocket' && is_numeric($text)) {
        $s['rocket_charge'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " রকেট চার্জ আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminLimitSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // চ্যানেল সেটিংস
    if ($text == 'চ্যানেল সেটিংস') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📢', '5424818078833715060') . " <b>চ্যানেল সেটিংস</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminChannelSettingsMenu()
        ]);
        exit;
    }
    if ($text == 'ফোর্স ইউজারনেম' || $text == 'Force Username') {
        setState($from_id, 'set_f_channel');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📢', '5424818078833715060') . " বাধ্যতামূলক চ্যানেল ইউজারনেম (@ চিহ্নসহ):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_f_channel' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['force_channel'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফোর্স চ্যানেল আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminChannelSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'অফিশিয়াল লিংক' || $text == 'Official Link') {
        setState($from_id, 'set_f_channel_url');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔗', '5271604874419647061') . " অফিশিয়াল চ্যানেল লিংক (URL):", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_f_channel_url' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['force_channel_url'] = $text;
        $s['official_channel'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " অফিশিয়াল লিংক আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminChannelSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == '📞 সাপোর্ট লিংক সেট' || $text == '📞 Set Support Link') {
        setState($from_id, 'set_support_id_link');
        bot('sendMessage', [
            'chat_id' => $chat_id, 
            'text' => pe('💬', '5443038326535759644') . " নতুন সাপোর্ট লিংক (URL):", 
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'set_support_id_link' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $s['support_id'] = $text;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " সাপোর্ট লিংক আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminChannelSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    // জয়েন চ্যানেল
    if ($text == 'জয়েন চ্যানেল ম্যানেজমেন্ট' || $text == 'Join Channel Management') {
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📢', '5424818078833715060') . " <b>জয়েন চ্যানেল ম্যানেজমেন্ট</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => getJoinChannelManageMenu()
        ]);
        exit;
    }
    if ($text == 'নতুন চ্যানেল যোগ করুন') {
        setState($from_id, 'wait_add_jc_title');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📢', '5424818078833715060') . " চ্যানেলের টাইটেল/নাম লিখুন:",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'wait_add_jc_title' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $ch_title = trim($text);
        if ($ch_title === '' || mb_strlen($ch_title) > 50) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক টাইটেল দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        setState($from_id, 'wait_add_jc_chatid', json_encode(['title' => $ch_title]));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🆔', '5253742260054409879') . " চ্যানেলের ইউজারনেম বা আইডি দিন (@channelusername অথবা -1001234567890):",
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    if ($state == 'wait_add_jc_chatid' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $ch_chatid = trim($text);
        if ($ch_chatid === '' || mb_strlen($ch_chatid) > 100) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক আইডি দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $step_data = json_decode($temp, true);
        if (!is_array($step_data)) { $step_data = []; }
        $step_data['chat_id'] = $ch_chatid;
        setState($from_id, 'wait_add_jc_url', json_encode($step_data));
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🔗', '5271604874419647061') . " চ্যানেলে জয়েন করার লিংক (URL):", 'parse_mode' => 'HTML']);
        exit;
    }
    if ($state == 'wait_add_jc_url' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $ch_url = trim($text);
        if (strpos($ch_url, 'http://') !== 0 && strpos($ch_url, 'https://') !== 0) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক লিংক দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $step_data = json_decode($temp, true);
        if (!is_array($step_data)) { $step_data = []; }
        $step_data['url'] = $ch_url;
        setState($from_id, 'wait_add_jc_color', json_encode($step_data));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🎨', '5253742260054409879') . " বাটনের রঙ বেছে নিন:",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => colorPickerKeyboard('jc_pickcolor')])
        ]);
        exit;
    }
    if ($text == '📋 সব চ্যানেল দেখুন') {
        showAdminChannelList($chat_id, $s);
        exit;
    }
    if ($state == 'wait_edit_jc_value' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $edit_info = json_decode($temp, true);
        if (!is_array($edit_info) || empty($edit_info['id']) || empty($edit_info['field'])) {
            setState($from_id, "");
            exit;
        }
        $edit_channels = $s['join_channels'] ?? [];
        $idx = findChannelIndex($edit_channels, $edit_info['id']);
        if ($idx === -1) { setState($from_id, ""); exit; }

        $field = $edit_info['field'];
        $new_val = trim($text);
        if ($field === 'url' && strpos($new_val, 'http://') !== 0 && strpos($new_val, 'https://') !== 0) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক লিংক দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $field_key = ($field === 'chatid') ? 'chat_id' : $field;
        $edit_channels[$idx][$field_key] = $new_val;
        $s['join_channels'] = $edit_channels;
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " চ্যানেল আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getJoinChannelManageMenu()]);
        setState($from_id, "");
        exit;
    }

    // সেন্ট্রাল শিট
    if ($text == 'সেন্ট্রাল শিট') {
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📊', '5231200819986047254') . " <b>সেন্ট্রাল শিট</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminCentralSheetMenu()
        ]);
        exit;
    }
    if ($text == 'ইনস্টাগ্রাম কন্ট্রোল' || $text == 'Instagram Control') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📸', '5210956306952758910') . " <b>ইনস্টাগ্রাম শিট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaControlMenu()]);
        exit;
    }
    if ($text == 'জিমেইল কন্ট্রোল' || $text == 'Gmail Control') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📧', '5253742260054409879') . " <b>জিমেইল শিট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getGmailControlMenu()]);
        exit;
    }
    if ($text == 'ফেসবুক কন্ট্রোল' || $text == 'Facebook Control') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('👥', '5460795800101594035') . " <b>ফেসবুক শিট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getFacebookControlMenu()]);
        exit;
    }
    if ($text == 'কুকিজ কন্ট্রোল' || $text == 'Cookies Control') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🍪', '5229064374403998351') . " <b>ফেসবুক কুকিজ শিট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getCookiesControlMenu()]);
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ কন্ট্রোল' || $text == 'Insta Cookies Control') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🍪', '5229064374403998351') . " <b>ইনস্টাগ্রাম কুকিজ শিট কন্ট্রোল</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaCookiesControlMenu()]);
        exit;
    }

    if ($text == '🆕 কাস্টম কাজ শিট' || $text == '🆕 Custom Task Sheets') {
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('📥','5406745015365943482').' <b>'.L($user_lang,'কাস্টম কাজের Central Sheet','Custom Task Sheets').'</b>','parse_mode'=>'HTML','reply_markup'=>getCustomTaskSheetsMenu()]);
        exit;
    }

    // Dynamic custom task CSV downloads/reports
    $custom_sheet_match = null;
    $custom_report_type = null;
    foreach (readTasks() as $ct) {
        $label = ($ct['emoji']??'🆕').' '.($ct['name']??'');
        if ($text === $label.' — 📥 CSV') { $custom_sheet_match=$ct; break; }
        if ($text === $label.' — ✅ ভালো আইডি') { $custom_sheet_match=$ct; $custom_report_type='approved'; break; }
        if ($text === $label.' — ❌ খারাপ আইডি') { $custom_sheet_match=$ct; $custom_report_type='rejected'; break; }
    }
    if ($custom_sheet_match !== null && $custom_report_type === null) {
        // IMPORTANT: sending the CSV is read-only. No seed/ID is deleted here.
        $path=taskCsvPath($custom_sheet_match['id']);
        if (!file_exists($path)) ensureTaskCsv($custom_sheet_match);
        normalizeTaskCsv($custom_sheet_match);
        if (file_exists($path) && filesize($path)>0) {
            bot('sendDocument',['chat_id'=>$chat_id,'document'=>new CURLFile($path),'caption'=>pe('📥','5406745015365943482').' '.$custom_sheet_match['name'].' CSV
🔒 Download করলে কোনো Seed/ID মুছে যাবে না।','parse_mode'=>'HTML']);
        } else {
            bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('📥','5406745015365943482').' এই কাজের কোনো ডাটা এখনো নেই।','parse_mode'=>'HTML']);
        }
        exit;
    }
    if ($custom_sheet_match !== null && $custom_report_type !== null) {
        setState($from_id, 'wait_custom_report:'.$custom_report_type.':'.$custom_sheet_match['id']);
        bot('sendMessage',['chat_id'=>$chat_id,'text'=>($custom_report_type==='approved'?pe('✅','5206607081334906820'):pe('❌','5210952531676504517'))." <b>{$custom_sheet_match['name']}</b>-এর ".($custom_report_type==='approved'?'ভালো':'খারাপ')." আইডি .csv ফাইল পাঠান।

📌 রিপোর্ট মিললে শুধু সংশ্লিষ্ট Seed/ID CSV থেকে যাবে; Download করলে যাবে না।",'parse_mode'=>'HTML','reply_markup'=>json_encode(['keyboard'=>[[makeBtn(L($user_lang,'🔙 সেন্ট্রাল শিট','🔙 Central Sheet'),'danger')]],'resize_keyboard'=>true])]);
        exit;
    }

    // CSV Downloads
    if ($text == 'ইনস্টাগ্রাম শিট ডাউনলোড' || $text == 'Download Instagram Sheet') {
        $path = "data/accounts_master.csv";
        if (file_exists($path) && filesize($path) > 50) {
            bot('sendDocument', ['chat_id' => $chat_id, 'document' => new CURLFile($path), 'caption' => pe('📥', '5406745015365943482') . " ইনস্টাগ্রাম পেন্ডিং শিট!", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " কোনো পেন্ডিং ইনস্টাগ্রাম ডাটা নেই।", 'parse_mode' => 'HTML']);
        }
        exit;
    }
    if ($text == 'জিমেইল শিট ডাউনলোড' || $text == 'Download Gmail Sheet') {
        $path = "data/gmail_master.csv";
        if (file_exists($path) && filesize($path) > 50) {
            bot('sendDocument', ['chat_id' => $chat_id, 'document' => new CURLFile($path), 'caption' => pe('📥', '5406745015365943482') . " জিমেইল পেন্ডিং শিট!", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " কোনো পেন্ডিং জিমেইল ডাটা নেই।", 'parse_mode' => 'HTML']);
        }
        exit;
    }
    if ($text == 'ফেসবুক শিট ডাউনলোড' || $text == 'Download Facebook Sheet') {
        $path = "data/facebook_master.csv";
        if (file_exists($path) && filesize($path) > 50) {
            bot('sendDocument', ['chat_id' => $chat_id, 'document' => new CURLFile($path), 'caption' => pe('📥', '5406745015365943482') . " ফেসবুক পেন্ডিং শিট!", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " কোনো পেন্ডিং ফেসবুক ডাটা নেই।", 'parse_mode' => 'HTML']);
        }
        exit;
    }
    if ($text == 'কুকিজ শিট ডাউনলোড' || $text == 'Download Cookies Sheet') {
        $path = "data/facebook_cookies_master.csv";
        if (file_exists($path) && filesize($path) > 50) {
            bot('sendDocument', ['chat_id' => $chat_id, 'document' => new CURLFile($path), 'caption' => pe('📥', '5406745015365943482') . " ফেসবুক কুকিজ পেন্ডিং শিট!", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " কোনো পেন্ডিং ফেসবুক কুকিজ ডাটা নেই।", 'parse_mode' => 'HTML']);
        }
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ শিট ডাউনলোড' || $text == '📥 ইনস্টাগ্রাম কুকিজ শিট ডাউনলোড' || $text == 'Download Insta Cookies Sheet') {
        $path = "data/instagram_cookies_master.csv";
        if (file_exists($path) && filesize($path) > 50) {
            bot('sendDocument', ['chat_id' => $chat_id, 'document' => new CURLFile($path), 'caption' => pe('📥', '5406745015365943482') . " ইনস্টাগ্রাম কুকিজ পেন্ডিং শিট!", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📥', '5406745015365943482') . " কোনো পেন্ডিং ইনস্টাগ্রাম কুকিজ ডাটা নেই।", 'parse_mode' => 'HTML']);
        }
        exit;
    }

    if ($text == 'ডাটাবেজ ক্লিনআপ' || $text == 'Database Cleanup') {
        $proofs = readDB('proofs.json');
        $pending_only = [];
        $cleared_count = 0;
        foreach ($proofs as $p) {
            if ($p['status'] == 'pending') { $pending_only[] = $p; } else { $cleared_count++; }
        }
        writeDB('proofs.json', $pending_only);
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🧹', '5445267414562389170') . " <b>ডাটাবেজ ক্লিনআপ সম্পন্ন!</b>\n\nমোট <b>{$cleared_count}</b> টি পুরনো কাজ ডিলিট হয়েছে।",
            'parse_mode' => 'HTML',
            'reply_markup' => getAdminCentralSheetMenu()
        ]);
        exit;
    }

    if ($text == 'ব্রডকাস্ট') {
        setState($from_id, 'wait_broadcast_msg');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📢', '5424818078833715060') . " <b>ব্রডকাস্ট প্যানেল</b>\n\nআপনি সকল ইউজারের কাছে যে মেসেজটি পাঠাতে চান তা এখানে পাঠান।",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 এডমিন মেনু', '🔙 Admin Menu'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }

    // CSV Approved/Rejected prompts
    if ($text == 'ইনস্টা ভালো আইডি' || $text == 'Insta Good ID') {
        setState($from_id, 'wait_insta_approved_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📸', '5210956306952758910') . " এপ্রুভড ইনস্টাগ্রাম .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'ইনস্টা খারাপ আইডি' || $text == 'Insta Bad ID') {
        setState($from_id, 'wait_insta_rejected_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📸', '5210956306952758910') . " রিজেক্টেড ইনস্টাগ্রাম .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'জিমেইল ভালো আইডি' || $text == 'Gmail Good ID') {
        setState($from_id, 'wait_gmail_approved_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📧', '5253742260054409879') . " এপ্রুভড জিমেইল .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'জিমেইল খারাপ আইডি' || $text == 'Gmail Bad ID') {
        setState($from_id, 'wait_gmail_rejected_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📧', '5253742260054409879') . " রিজেক্টেড জিমেইল .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'ফেসবুক ভালো আইডি' || $text == 'Facebook Good ID') {
        setState($from_id, 'wait_fb_approved_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('👥', '5460795800101594035') . " এপ্রুভড ফেসবুক .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'ফেসবুক খারাপ আইডি' || $text == 'Facebook Bad ID') {
        setState($from_id, 'wait_fb_rejected_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('👥', '5460795800101594035') . " রিজেক্টেড ফেসবুক .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'কুকিজ ভালো আইডি' || $text == 'Cookies Good ID') {
        setState($from_id, 'wait_cookies_approved_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🍪', '5229064374403998351') . " এপ্রুভড ফেসবুক কুকিজ .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'কুকিজ খারাপ আইডি' || $text == 'Cookies Bad ID') {
        setState($from_id, 'wait_cookies_rejected_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('🍪', '5229064374403998351') . " রিজেক্টেড ফেসবুক কুকিজ .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ ভালো আইডি' || $text == 'Insta Cookies Good ID') {
        setState($from_id, 'wait_insta_cookies_approved_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📸', '5210956306952758910') . " এপ্রুভড ইনস্টাগ্রাম কুকিজ .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ খারাপ আইডি' || $text == 'Insta Cookies Bad ID') {
        setState($from_id, 'wait_insta_cookies_rejected_csv');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('📸', '5210956306952758910') . " রিজেক্টেড ইনস্টাগ্রাম কুকিজ .csv ফাইল পাঠান:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 সেন্ট্রাল শিট', '🔙 Central Sheet'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }

    // কাজের মূল্য
    if ($text == 'কাজের মূল্য সেট') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⚙️', '5341715473882955310') . " প্রতিটি কাজের মূল্য পরিবর্তন:", 'parse_mode' => 'HTML', 'reply_markup' => getAdminRewardSettingsMenu()]);
        exit;
    }
    if ($text == 'ইনস্টা কাজের মূল্য' || $text == 'Insta Task Price') {
        setState($from_id, 'set_val_insta');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " ইনস্টা কাজের রেট:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_val_insta' && is_numeric($text)) {
        $s['reward_instagram'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টা রেট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'জিমেইল কাজের মূল্য' || $text == 'Gmail Task Price') {
        setState($from_id, 'set_val_gmail');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " জিমেইল কাজের রেট:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_val_gmail' && is_numeric($text)) {
        $s['reward_gmail'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " জিমেইল রেট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'ফেসবুক কাজের মূল্য' || $text == 'Facebook Task Price') {
        setState($from_id, 'set_val_fb');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " ফেসবুক কাজের রেট:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_val_fb' && is_numeric($text)) {
        $s['reward_facebook'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক রেট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'কুকিজ কাজের মূল্য' || $text == 'Cookies Task Price') {
        setState($from_id, 'set_val_fb_cookies');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " ফেসবুক কুকিজ কাজের রেট:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_val_fb_cookies' && is_numeric($text)) {
        $s['reward_facebook_cookies'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " কুকিজ রেট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }
    if ($text == 'ইনস্টা কুকিজ কাজের মূল্য' || $text == 'Insta Cookies Task Price') {
        setState($from_id, 'set_val_insta_cookies');
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " ইনস্টা কুকিজ কাজের রেট:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])]);
        exit;
    }
    if ($state == 'set_val_insta_cookies' && is_numeric($text)) {
        $s['reward_instagram_cookies'] = floatval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টা কুকিজ রেট আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
        setState($from_id, "");
        exit;
    }

    if ($text == 'পেন্ডিং উইথড্র') {
        showAdminWithdrawList($chat_id);
        exit;
    }

    // উইথড্র মেথড
    if ($text == 'উইথড্র মেথড সেটিংস') {
        setState($from_id, "");
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🧩', '5406745015365943482') . " <b>ডাইনামিক উইথড্রাল মেথড ম্যানেজমেন্ট</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => getMethodManageMenu()
        ]);
        exit;
    }
    if ($text == 'নতুন মেথড যোগ করুন') {
        setState($from_id, 'wait_add_method_name');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🧩', '5406745015365943482') . " নতুন মেথডের নাম:",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'wait_add_method_name' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $method_name = trim($text);
        if ($method_name === '' || mb_strlen($method_name) > 50) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক নাম দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $existing_methods = readMethods();
        foreach ($existing_methods as $em) {
            if (strcasecmp(trim($em['name']), $method_name) === 0) {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " এই নামে মেথড আছে।", 'parse_mode' => 'HTML']);
                exit;
            }
        }
        setState($from_id, 'wait_add_method_field_label', json_encode(['name' => $method_name]));
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📝', '5253742260054409879') . " ফিল্ড লেবেল (যেমন: Binance Pay ID):",
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    if ($state == 'wait_add_method_field_label' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $field_label = trim($text);
        if ($field_label === '' || mb_strlen($field_label) > 80) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক ফিল্ড লেবেল দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $step_data = json_decode($temp, true);
        if (!is_array($step_data)) { $step_data = []; }
        $step_data['field_label'] = $field_label;
        setState($from_id, 'wait_add_method_min', json_encode($step_data));
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " সর্বনিম্ন উইথড্র (BDT):", 'parse_mode' => 'HTML']);
        exit;
    }
    if ($state == 'wait_add_method_min' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        if (!is_numeric($text) || floatval($text) < 0) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক সংখ্যা দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $step_data = json_decode($temp, true);
        if (!is_array($step_data)) { $step_data = []; }
        $step_data['min'] = floatval($text);
        setState($from_id, 'wait_add_method_charge', json_encode($step_data));
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💰', '5233326571099534068') . " চার্জ (৳, না থাকলে 0):", 'parse_mode' => 'HTML']);
        exit;
    }
    if ($state == 'wait_add_method_charge' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        if (!is_numeric($text) || floatval($text) < 0) {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক সংখ্যা দিন।", 'parse_mode' => 'HTML']);
            exit;
        }
        $step_data = json_decode($temp, true);
        if (!is_array($step_data)) { $step_data = []; }
        $step_data['charge'] = floatval($text);
        $existing_methods = readMethods();
        $new_method = [
            'id' => genMethodId($step_data['name']),
            'name' => $step_data['name'],
            'field_label' => $step_data['field_label'],
            'min' => $step_data['min'],
            'charge' => $step_data['charge'],
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $existing_methods[] = $new_method;
        writeMethodsDB($existing_methods);
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('✅', '5206607081334906820') . " নতুন মেথড <b>{$new_method['name']}</b> যোগ হয়েছে!",
            'parse_mode' => 'HTML',
            'reply_markup' => getMethodManageMenu()
        ]);
        setState($from_id, "");
        exit;
    }
    if ($text == '📋 সব মেথড দেখুন') {
        showAdminMethodList($chat_id);
        exit;
    }
    if ($state == 'wait_edit_method_value' && $text != '🔙 বটের সেটিংস' && $text != '🔙 Bot Settings') {
        $edit_info = json_decode($temp, true);
        if (!is_array($edit_info) || empty($edit_info['id']) || empty($edit_info['field'])) {
            setState($from_id, "");
            exit;
        }
        $edit_methods = readMethods();
        $idx = findMethodIndex($edit_methods, $edit_info['id']);
        if ($idx === -1) { setState($from_id, ""); exit; }
        $field = $edit_info['field'];
        if (in_array($field, ['min', 'charge'])) {
            if (!is_numeric($text) || floatval($text) < 0) {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " সঠিক সংখ্যা দিন।", 'parse_mode' => 'HTML']);
                exit;
            }
            $edit_methods[$idx][$field] = floatval($text);
        } else {
            $new_val = trim($text);
            $edit_methods[$idx][$field] = $new_val;
        }
        writeMethodsDB($edit_methods);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " মেথড আপডেট!", 'parse_mode' => 'HTML', 'reply_markup' => getMethodManageMenu()]);
        setState($from_id, "");
        exit;
    }

    // সিড কন্ট্রোল
    if ($text == '📊 সিড কন্ট্রোল') {
        setState($from_id, "");
        $s = readDB('settings.json');
        $insta_status = ($s['seed_instagram_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        $fbc_status = ($s['seed_fbcookies_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        $gmail_status = ($s['seed_gmail_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        
        $menu = [
            [makeBtn("{$insta_status} 📸 Instagram সিড", 'primary')],
            [makeBtn("{$fbc_status} 🍪 Facebook Cookies সিড", 'primary')],
            [makeBtn("{$gmail_status} 📧 Gmail সিড", 'primary')],
            [makeBtn('🔙 বটের সেটিংস', 'danger')]
        ];
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📊', '5231200819986047254') . " <b>সিড কন্ট্রোল প্যানেল</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if (strpos($text, '📸 Instagram সিড') !== false) {
        setState($from_id, "");
        $s = readDB('settings.json');
        $url = $s['seed_instagram_url'] ?? '';
        $sheet = $s['seed_instagram_sheet'] ?? 'Instagram';
        $status = ($s['seed_instagram_status'] ?? 'off') == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        $url_display = empty($url) ? '❌ সেট করা হয়নি' : '✅ ' . substr($url, 0, 50) . '...';
        $toggle_label = ($s['seed_instagram_status'] ?? 'off') == 'on' ? '🔴 বন্ধ করুন' : '🟢 চালু করুন';
        $menu = [
            [makeBtn('🔗 URL সেট করুন', 'primary'), makeBtn('📄 Sheet নাম সেট', 'primary')],
            [makeBtn($toggle_label, ($s['seed_instagram_status'] ?? 'off') == 'on' ? 'danger' : 'success')],
            [makeBtn('🗑️ URL মুছুন', 'danger')],
            [makeBtn('🔙 সিড কন্ট্রোল', 'primary')]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📸', '5210956306952758910') . " <b>Instagram সিড</b>\n\n🔗 URL: {$url_display}\n📄 Sheet: <code>{$sheet}</code>\n📊 স্ট্যাটাস: {$status}",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if (strpos($text, '🍪 Facebook Cookies সিড') !== false) {
        setState($from_id, "");
        $s = readDB('settings.json');
        $url = $s['seed_fbcookies_url'] ?? '';
        $sheet = $s['seed_fbcookies_sheet'] ?? 'FBCookies';
        $status = ($s['seed_fbcookies_status'] ?? 'off') == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        $url_display = empty($url) ? '❌ সেট করা হয়নি' : '✅ ' . substr($url, 0, 50) . '...';
        $toggle_label = ($s['seed_fbcookies_status'] ?? 'off') == 'on' ? '🔴 বন্ধ করুন' : '🟢 চালু করুন';
        $menu = [
            [makeBtn('🔗 URL সেট করুন', 'primary'), makeBtn('📄 Sheet নাম সেট', 'primary')],
            [makeBtn($toggle_label, ($s['seed_fbcookies_status'] ?? 'off') == 'on' ? 'danger' : 'success')],
            [makeBtn('🗑️ URL মুছুন', 'danger')],
            [makeBtn('🔙 সিড কন্ট্রোল', 'primary')]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🍪', '5229064374403998351') . " <b>Facebook Cookies সিড</b>\n\n🔗 URL: {$url_display}\n📄 Sheet: <code>{$sheet}</code>\n📊 স্ট্যাটাস: {$status}",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if (strpos($text, '📧 Gmail সিড') !== false) {
        setState($from_id, "");
        $s = readDB('settings.json');
        $url = $s['seed_gmail_url'] ?? '';
        $sheet = $s['seed_gmail_sheet'] ?? 'Gmail';
        $status = ($s['seed_gmail_status'] ?? 'off') == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        $url_display = empty($url) ? '❌ সেট করা হয়নি' : '✅ ' . substr($url, 0, 50) . '...';
        $toggle_label = ($s['seed_gmail_status'] ?? 'off') == 'on' ? '🔴 বন্ধ করুন' : '🟢 চালু করুন';
        $menu = [
            [makeBtn('🔗 URL সেট করুন', 'primary'), makeBtn('📄 Sheet নাম সেট', 'primary')],
            [makeBtn($toggle_label, ($s['seed_gmail_status'] ?? 'off') == 'on' ? 'danger' : 'success')],
            [makeBtn('🗑️ URL মুছুন', 'danger')],
            [makeBtn('🔙 সিড কন্ট্রোল', 'primary')]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📧', '5253742260054409879') . " <b>Gmail সিড</b>\n\n🔗 URL: {$url_display}\n📄 Sheet: <code>{$sheet}</code>\n📊 স্ট্যাটাস: {$status}",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($text == '🔙 সিড কন্ট্রোল') {
        setState($from_id, "");
        $s = readDB('settings.json');
        $insta_status = ($s['seed_instagram_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        $fbc_status = ($s['seed_fbcookies_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        $gmail_status = ($s['seed_gmail_status'] ?? 'off') == 'on' ? '🟢' : '🔴';
        $menu = [
            [makeBtn("{$insta_status} 📸 Instagram সিড", 'primary')],
            [makeBtn("{$fbc_status} 🍪 Facebook Cookies সিড", 'primary')],
            [makeBtn("{$gmail_status} 📧 Gmail সিড", 'primary')],
            [makeBtn('🔙 বটের সেটিংস', 'danger')]
        ];
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📊', '5231200819986047254') . " <b>সিড কন্ট্রোল প্যানেল</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }

    // ব্যাকআপ
    if ($text == '💾 ব্যাকআপ') {
        setState($from_id, "");
        $s = readDB('settings.json');
        $backup_status = ($s['backup_status'] ?? 'off') == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        $interval = $s['backup_interval'] ?? 6;
        $last_time = intval($s['backup_last_time'] ?? 0);
        $last_str = $last_time > 0 ? date('Y-m-d H:i:s', $last_time) : 'এখনো হয়নি';
        
        $menu = [
            [makeBtn('📥 এখনই ব্যাকআপ নিন', 'success')],
            [makeBtn('📤 ব্যাকআপ রিস্টোর', 'primary')],
            [makeBtn('⏰ অটো ব্যাকআপ: ' . (($s['backup_status'] ?? 'off') == 'on' ? 'চালু' : 'বন্ধ'), ($s['backup_status'] ?? 'off') == 'on' ? 'danger' : 'success')],
            [makeBtn('🕒 ইন্টারভাল সেট (' . $interval . ' ঘণ্টা)', 'primary')],
            [makeBtn('🔙 বটের সেটিংস', 'danger')]
        ];
        
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('💾', '5406745015365943482') . " <b>ব্যাকআপ সিস্টেম</b>\n\n📊 স্ট্যাটাস: {$backup_status}\n🕒 ইন্টারভাল: {$interval} ঘণ্টা\n⏰ শেষ ব্যাকআপ: {$last_str}",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($text == '📥 এখনই ব্যাকআপ নিন') {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('⏳', '5386367538735104399') . " ব্যাকআপ তৈরি হচ্ছে...", 'parse_mode' => 'HTML']);
        $result = createBackupZip();
        if ($result['ok']) {
            bot('sendDocument', [
                'chat_id' => $chat_id,
                'document' => new CURLFile($result['path']),
                'caption' => pe('💾', '5406745015365943482') . " <b>ব্যাকআপ সম্পন্ন!</b>\n\n📦 ফাইল: <code>{$result['name']}</code>\n📊 ফাইল সংখ্যা: {$result['files']}\n📏 সাইজ: " . round($result['size'] / 1024, 2) . " KB",
                'parse_mode' => 'HTML'
            ]);
            @unlink($result['path']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ব্যাকআপ ব্যর্থ: " . $result['error'], 'parse_mode' => 'HTML']);
        }
        exit;
    }
    if ($text == '📤 ব্যাকআপ রিস্টোর') {
        setState($from_id, 'wait_restore_zip');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('📤', '5406745015365943482') . " <b>ব্যাকআপ রিস্টোর</b>\n\nআপনার <code>.zip</code> ফাইলটি পাঠান।",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 ব্যাকআপ', 'primary')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($text == '⏰ অটো ব্যাকআপ: চালু' || $text == '⏰ অটো ব্যাকআপ: বন্ধ') {
        $s = readDB('settings.json');
        $s['backup_status'] = ($s['backup_status'] ?? 'off') == 'on' ? 'off' : 'on';
        writeDB('settings.json', $s);
        $status_text = $s['backup_status'] == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " অটো ব্যাকআপ: <b>{$status_text}</b>", 'parse_mode' => 'HTML']);
        exit;
    }
    if (strpos($text, '🕒 ইন্টারভাল সেট') === 0) {
        setState($from_id, 'wait_backup_interval');
        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => pe('🕒', '5386367538735104399') . " নতুন ইন্টারভাল (ঘণ্টায়):",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 ব্যাকআপ', 'primary')]], 'resize_keyboard' => true])
        ]);
        exit;
    }
    if ($state == 'wait_backup_interval' && is_numeric($text)) {
        $s = readDB('settings.json');
        $s['backup_interval'] = intval($text);
        writeDB('settings.json', $s);
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইন্টারভাল: <b>{$text} ঘণ্টা</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 ব্যাকআপ', 'primary')]], 'resize_keyboard' => true])]);
        setState($from_id, "");
        exit;
    }
    if ($text == '🔙 ব্যাকআপ') {
        setState($from_id, "");
        $s = readDB('settings.json');
        $backup_status = ($s['backup_status'] ?? 'off') == 'on' ? '🟢 চালু' : '🔴 বন্ধ';
        $interval = $s['backup_interval'] ?? 6;
        $menu = [
            [makeBtn('📥 এখনই ব্যাকআপ নিন', 'success')],
            [makeBtn('📤 ব্যাকআপ রিস্টোর', 'primary')],
            [makeBtn('⏰ অটো ব্যাকআপ: ' . (($s['backup_status'] ?? 'off') == 'on' ? 'চালু' : 'বন্ধ'), ($s['backup_status'] ?? 'off') == 'on' ? 'danger' : 'success')],
            [makeBtn('🕒 ইন্টারভাল সেট (' . $interval . ' ঘণ্টা)', 'primary')],
            [makeBtn('🔙 বটের সেটিংস', 'danger')]
        ];
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('💾', '5406745015365943482') . " <b>ব্যাকআপ সিস্টেম</b>", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => $menu, 'resize_keyboard' => true])]);
        exit;
    }

    // ব্যালেন্স এডিট
    if ($state == 'wait_balance_add_amount' && is_numeric($text)) {
        $target_uid = $temp;
        $add_amount = floatval($text);
        if (isset($users[$target_uid])) {
            $users[$target_uid]['balance'] += $add_amount;
            $users[$target_uid]['total_income'] += $add_amount;
            writeDB('users.json', $users);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " <code>{$target_uid}</code> এ ৳<code>{$add_amount}</code> যোগ হয়েছে!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminMenu()]);
            bot('sendMessage', ['chat_id' => $target_uid, 'text' => pe('💰', '5233326571099534068') . " <b>ব্যালেন্স!</b> ৳<code>{$add_amount}</code> যোগ হয়েছে।", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ইউজার নেই।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminMenu()]);
        }
        setState($from_id, "");
        exit;
    }
    if ($state == 'wait_balance_sub_amount' && is_numeric($text)) {
        $target_uid = $temp;
        $sub_amount = floatval($text);
        if (isset($users[$target_uid])) {
            $users[$target_uid]['balance'] = max(0, $users[$target_uid]['balance'] - $sub_amount);
            writeDB('users.json', $users);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " <code>{$target_uid}</code> থেকে ৳<code>{$sub_amount}</code> কাটা হয়েছে!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminMenu()]);
            bot('sendMessage', ['chat_id' => $target_uid, 'text' => pe('⚠️', '5447644880824181073') . " <b>ব্যালেন্স কর্তন!</b> ৳<code>{$sub_amount}</code> কাটা হয়েছে।", 'parse_mode' => 'HTML']);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ইউজার নেই।", 'parse_mode' => 'HTML', 'reply_markup' => getAdminMenu()]);
        }
        setState($from_id, "");
        exit;
    }
}

// ব্রডকাস্ট প্রসেসিং
if (strval($from_id) === strval(ADMIN_ID) && $state == 'wait_broadcast_msg' && $text != '🔙 এডমিন মেনু' && $text != '🔙 Admin Menu') {
    $all_users = readDB('users.json');
    $total_sent = 0;
    $total_failed = 0;
    
    $status_msg = bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('⏳', '5386367538735104399') . " ব্রডকাস্ট শুরু হয়েছে...",
        'parse_mode' => 'HTML'
    ]);
    $status_msg_id = $status_msg['result']['message_id'] ?? null;
    
    foreach ($all_users as $uid => $user_data) {
        $res = bot('copyMessage', [
            'chat_id' => $uid,
            'from_chat_id' => $chat_id,
            'message_id' => $message['message_id']
        ]);
        if (isset($res['ok']) && $res['ok'] == true) { $total_sent++; } else { $total_failed++; }
    }
    
    if ($status_msg_id) {
        bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $status_msg_id]);
    }
    
    bot('sendMessage', [
        'chat_id' => $chat_id,
        'text' => pe('📢', '5424818078833715060') . " <b>ব্রডকাস্ট সম্পন্ন!</b>\n\n✅ সফল: <b>{$total_sent}</b>\n❌ ব্যর্থ: <b>{$total_failed}</b>",
        'parse_mode' => 'HTML',
        'reply_markup' => getAdminMenu()
    ]);
    setState($from_id, "");
    exit;
}

// CSV Processing
$document = $message['document'] ?? null;
if ($document && strval($from_id) === strval(ADMIN_ID)) {
    $file_id = $document['file_id'];
    $file_info = bot('getFile', ['file_id' => $file_id]);
    $file_path = $file_info['result']['file_path'] ?? null;
    
    if ($state == 'wait_restore_zip') {
        $ext = strtolower(pathinfo($file_path ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " শুধু <code>.zip</code> ফাইল গ্রহণ করা হয়।", 'parse_mode' => 'HTML']);
            exit;
        }
        $file_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;
        $zip_content = file_get_contents($file_url);
        $temp_zip = 'data/restore_temp_' . uniqid() . '.zip';
        file_put_contents($temp_zip, $zip_content);
        
        $result = restoreBackupZip($temp_zip);
        @unlink($temp_zip);
        
        if ($result['ok']) {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('✅', '5206607081334906820') . " <b>ব্যাকআপ রিস্টোর সম্পন্ন!</b>\n\n📊 {$result['extracted']} টি ফাইল পুনরুদ্ধার হয়েছে।",
                'parse_mode' => 'HTML',
                'reply_markup' => getAdminMenu()
            ]);
        } else {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " রিস্টোর ব্যর্থ: " . $result['error'], 'parse_mode' => 'HTML']);
        }
        setState($from_id, "");
        exit;
    }
    
    if ($file_path) {
        $file_url = "https://api.telegram.org/file/bot" . API_KEY . "/" . $file_path;
        $csv_data = file_get_contents($file_url);
        
        $bom = pack('H*','EFBBBF');
        $csv_data = preg_replace("/^$bom/", '', $csv_data);
        $lines = explode("\n", str_replace("\r", "", $csv_data));
        
        $processed_count = 0;
        $delimiter = ',';

        foreach ($lines as $first_line) {
            if (!empty(trim($first_line))) {
                if (substr_count($first_line, ';') > substr_count($first_line, ',')) { $delimiter = ';'; }
                break;
            }
        }

        // Custom task reports: Download is read-only; rows are removed only after a
        // matching Good/Bad report is processed.
        if (strpos($state, 'wait_custom_report:') === 0) {
            $parts = explode(':', $state, 3);
            $report_type = $parts[1] ?? '';
            $custom_task_id = $parts[2] ?? '';
            if (($report_type === 'approved' || $report_type === 'rejected') && $custom_task_id !== '') {
                processDynamicTaskReport($custom_task_id, $report_type === 'approved', $lines, $delimiter, $chat_id, $from_id, $user_lang);
                exit;
            }
        }
        
        // ইনস্টা এপ্রুভড
        if ($state == 'wait_insta_approved_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'instagram') {
                        $username = trim($p['username']);
                        if (rowContainsValue($row, $username)) {
                            $proofs[$k]['status'] = 'approved';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $reward = floatval($s['reward_instagram']);
                            $users[$matched_user_id]['balance'] += $reward;
                            $users[$matched_user_id]['total_income'] += $reward;
                            $users[$matched_user_id]['completed_tasks'] += 1;
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $accounts_to_remove[] = ['id' => $username, 'two_fa' => $p['data']];
                            $referrer = $users[$matched_user_id]['ref_by'] ?? 0;
                            if (strval($referrer) != "0" && isset($users[$referrer])) {
                                $comm_pct = floatval($s['ref_commission'] ?? 5);
                                $comm = $reward * ($comm_pct / 100);
                                $users[$referrer]['balance'] += $comm;
                                $users[$referrer]['ref_income'] += $comm;
                                bot('sendMessage', ['chat_id' => $referrer, 'text' => pe('👥', '5460795800101594035') . " <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>", 'parse_mode' => 'HTML']);
                            }
                            writeDB('users.json', $users);
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('✅', '5206607081334906820') . " কাজ এপ্রুভ! ৳{$reward} যোগ হয়েছে ({$username})।", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'accounts_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টাগ্রাম ভালো আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // ইনস্টা রিজেক্টেড
        if ($state == 'wait_insta_rejected_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'instagram') {
                        $username = trim($p['username']);
                        if (rowContainsValue($row, $username)) {
                            $proofs[$k]['status'] = 'rejected';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $users[$matched_user_id]['rejected_tasks'] = ($users[$matched_user_id]['rejected_tasks'] ?? 0) + 1;
                            writeDB('users.json', $users);
                            $accounts_to_remove[] = ['id' => $username, 'two_fa' => $p['data']];
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('❌', '5210952531676504517') . " কাজ বাতিল! ({$username})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'accounts_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ইনস্টাগ্রাম খারাপ আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // জিমেইল এপ্রুভড
        if ($state == 'wait_gmail_approved_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'gmail') {
                        $email = trim($p['email']);
                        if (rowContainsValue($row, $email)) {
                            $proofs[$k]['status'] = 'approved';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $reward = floatval($s['reward_gmail']);
                            $users[$matched_user_id]['balance'] += $reward;
                            $users[$matched_user_id]['total_income'] += $reward;
                            $users[$matched_user_id]['completed_tasks'] += 1;
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $accounts_to_remove[] = ['email' => $email];
                            $referrer = $users[$matched_user_id]['ref_by'] ?? 0;
                            if (strval($referrer) != "0" && isset($users[$referrer])) {
                                $comm_pct = floatval($s['ref_commission'] ?? 5);
                                $comm = $reward * ($comm_pct / 100);
                                $users[$referrer]['balance'] += $comm;
                                $users[$referrer]['ref_income'] += $comm;
                                bot('sendMessage', ['chat_id' => $referrer, 'text' => pe('👥', '5460795800101594035') . " <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>", 'parse_mode' => 'HTML']);
                            }
                            writeDB('users.json', $users);
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('✅', '5206607081334906820') . " কাজ এপ্রুভ! ৳{$reward} ({$email})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'gmail_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " জিমেইল ভালো আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getGmailControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // জিমেইল রিজেক্টেড
        if ($state == 'wait_gmail_rejected_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'gmail') {
                        $email = trim($p['email']);
                        if (rowContainsValue($row, $email)) {
                            $proofs[$k]['status'] = 'rejected';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $users[$matched_user_id]['rejected_tasks'] = ($users[$matched_user_id]['rejected_tasks'] ?? 0) + 1;
                            writeDB('users.json', $users);
                            $accounts_to_remove[] = ['email' => $email];
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('❌', '5210952531676504517') . " কাজ বাতিল! ({$email})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'gmail_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " জিমেইল খারাপ আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getGmailControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // ফেসবুক এপ্রুভড
        if ($state == 'wait_fb_approved_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'facebook') {
                        $uid = trim($p['uid']);
                        if (rowContainsValue($row, $uid)) {
                            $proofs[$k]['status'] = 'approved';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $reward = floatval($s['reward_facebook']);
                            $users[$matched_user_id]['balance'] += $reward;
                            $users[$matched_user_id]['total_income'] += $reward;
                            $users[$matched_user_id]['completed_tasks'] += 1;
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $accounts_to_remove[] = ['id' => $uid, 'two_fa' => $p['data']];
                            $referrer = $users[$matched_user_id]['ref_by'] ?? 0;
                            if (strval($referrer) != "0" && isset($users[$referrer])) {
                                $comm_pct = floatval($s['ref_commission'] ?? 5);
                                $comm = $reward * ($comm_pct / 100);
                                $users[$referrer]['balance'] += $comm;
                                $users[$referrer]['ref_income'] += $comm;
                                bot('sendMessage', ['chat_id' => $referrer, 'text' => pe('👥', '5460795800101594035') . " <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>", 'parse_mode' => 'HTML']);
                            }
                            writeDB('users.json', $users);
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('✅', '5206607081334906820') . " কাজ এপ্রুভ! ৳{$reward} (UID: {$uid})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'facebook_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক ভালো আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getFacebookControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // ফেসবুক রিজেক্টেড
        if ($state == 'wait_fb_rejected_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'facebook') {
                        $uid = trim($p['uid']);
                        if (rowContainsValue($row, $uid)) {
                            $proofs[$k]['status'] = 'rejected';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $users[$matched_user_id]['rejected_tasks'] = ($users[$matched_user_id]['rejected_tasks'] ?? 0) + 1;
                            writeDB('users.json', $users);
                            $accounts_to_remove[] = ['id' => $uid, 'two_fa' => $p['data']];
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('❌', '5210952531676504517') . " কাজ বাতিল! (UID: {$uid})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'facebook_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ফেসবুক খারাপ আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getFacebookControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // কুকিজ এপ্রুভড
        if ($state == 'wait_cookies_approved_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'facebook_cookies') {
                        $uid = trim($p['uid']);
                        if (rowContainsValue($row, $uid)) {
                            $proofs[$k]['status'] = 'approved';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $reward = floatval($s['reward_facebook_cookies'] ?? 8.00);
                            $users[$matched_user_id]['balance'] += $reward;
                            $users[$matched_user_id]['total_income'] += $reward;
                            $users[$matched_user_id]['completed_tasks'] += 1;
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $accounts_to_remove[] = ['uid' => $uid];
                            $referrer = $users[$matched_user_id]['ref_by'] ?? 0;
                            if (strval($referrer) != "0" && isset($users[$referrer])) {
                                $comm_pct = floatval($s['ref_commission'] ?? 5);
                                $comm = $reward * ($comm_pct / 100);
                                $users[$referrer]['balance'] += $comm;
                                $users[$referrer]['ref_income'] += $comm;
                                bot('sendMessage', ['chat_id' => $referrer, 'text' => pe('👥', '5460795800101594035') . " <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>", 'parse_mode' => 'HTML']);
                            }
                            writeDB('users.json', $users);
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('✅', '5206607081334906820') . " কাজ এপ্রুভ! ৳{$reward} (UID: {$uid})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'facebook_cookies_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ফেসবুক কুকিজ ভালো আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getCookiesControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // কুকিজ রিজেক্টেড
        if ($state == 'wait_cookies_rejected_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'facebook_cookies') {
                        $uid = trim($p['uid']);
                        if (rowContainsValue($row, $uid)) {
                            $proofs[$k]['status'] = 'rejected';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $users[$matched_user_id]['rejected_tasks'] = ($users[$matched_user_id]['rejected_tasks'] ?? 0) + 1;
                            writeDB('users.json', $users);
                            $accounts_to_remove[] = ['uid' => $uid];
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('❌', '5210952531676504517') . " কাজ বাতিল! (UID: {$uid})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'facebook_cookies_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ফেসবুক কুকিজ খারাপ আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getCookiesControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // ইনস্টা কুকিজ এপ্রুভড
        if ($state == 'wait_insta_cookies_approved_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'instagram_cookies') {
                        $username = trim($p['username']);
                        if (rowContainsValue($row, $username)) {
                            $proofs[$k]['status'] = 'approved';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $reward = floatval($s['reward_instagram_cookies'] ?? 8.00);
                            $users[$matched_user_id]['balance'] += $reward;
                            $users[$matched_user_id]['total_income'] += $reward;
                            $users[$matched_user_id]['completed_tasks'] += 1;
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $accounts_to_remove[] = ['username' => $username];
                            $referrer = $users[$matched_user_id]['ref_by'] ?? 0;
                            if (strval($referrer) != "0" && isset($users[$referrer])) {
                                $comm_pct = floatval($s['ref_commission'] ?? 5);
                                $comm = $reward * ($comm_pct / 100);
                                $users[$referrer]['balance'] += $comm;
                                $users[$referrer]['ref_income'] += $comm;
                                bot('sendMessage', ['chat_id' => $referrer, 'text' => pe('👥', '5460795800101594035') . " <b>রেফার কমিশন!</b> ৳<code>{$comm}</code>", 'parse_mode' => 'HTML']);
                            }
                            writeDB('users.json', $users);
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('✅', '5206607081334906820') . " কাজ এপ্রুভ! ৳{$reward} ({$username})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'instagram_cookies_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " ইনস্টাগ্রাম কুকিজ ভালো আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaCookiesControlMenu()]);
            setState($from_id, "");
            exit;
        }
        // ইনস্টা কুকিজ রিজেক্টেড
        if ($state == 'wait_insta_cookies_rejected_csv') {
            $accounts_to_remove = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) continue;
                $row = str_getcsv($line, $delimiter);
                if (empty($row)) continue;
                $proofs = readDB('proofs.json');
                foreach ($proofs as $k => $p) {
                    if ($p['status'] == 'pending' && $p['task_type'] == 'instagram_cookies') {
                        $username = trim($p['username']);
                        if (rowContainsValue($row, $username)) {
                            $proofs[$k]['status'] = 'rejected';
                            writeDB('proofs.json', $proofs);
                            $matched_user_id = $p['user_id'];
                            $users[$matched_user_id]['review_tasks'] = max(0, $users[$matched_user_id]['review_tasks'] - 1);
                            $users[$matched_user_id]['rejected_tasks'] = ($users[$matched_user_id]['rejected_tasks'] ?? 0) + 1;
                            writeDB('users.json', $users);
                            $accounts_to_remove[] = ['username' => $username];
                            bot('sendMessage', ['chat_id' => $matched_user_id, 'text' => pe('❌', '5210952531676504517') . " কাজ বাতিল! ({$username})", 'parse_mode' => 'HTML']);
                            $processed_count++;
                            break;
                        }
                    }
                }
            }
            removeCSVRowsBulk($accounts_to_remove, 'instagram_cookies_master.csv');
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('❌', '5210952531676504517') . " ইনস্টাগ্রাম কুকিজ খারাপ আইডি প্রসেস সম্পন্ন! মোট: <b>{$processed_count}</b>", 'parse_mode' => 'HTML', 'reply_markup' => getInstaCookiesControlMenu()]);
            setState($from_id, "");
            exit;
        }
    }
}

// ভিডিও ফাইল
$video_file = $message['video'] ?? null;
if ($video_file && strval($from_id) === strval(ADMIN_ID) && $state == 'wait_add_video') {
    $file_id = $video_file['file_id'];
    $s['work_videos'][] = $file_id;
    writeDB('settings.json', $s);
    bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('✅', '5206607081334906820') . " নতুন ভিডিও যোগ হয়েছে!", 'parse_mode' => 'HTML', 'reply_markup' => getAdminSettingsMenu()]);
    setState($from_id, "");
    exit;
}

// ═══════════════════════════════════════════════════════
// Callback Query Handler
// ═══════════════════════════════════════════════════════
if ($callback) {
    $cb_data = $callback['data'];
    $cb_id = $callback['id'];
    $msg_id = $callback['message']['message_id'];

    if ($cb_data == 'lang_set_bn' || $cb_data == 'lang_set_en') {
        $new_lang = ($cb_data == 'lang_set_bn') ? 'bn' : 'en';
        $users[$from_id]['language'] = $new_lang;
        writeDB('users.json', $users);
        bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
        bot('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $msg_id,
            'text' => pe('✅', '5206607081334906820') . " " . t('language_set_' . $new_lang, $new_lang),
            'parse_mode' => 'HTML'
        ]);
        exit;
    }
    
    if (strval($from_id) === strval(ADMIN_ID)) {
        // withdraw list
        if ($cb_data == 'list_wd') {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminWithdrawList($chat_id);
            exit;
        }

        // ═══ কাজ ম্যানেজমেন্ট Callback ═══
        if ($cb_data == 'list_tsk') {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminTaskList($chat_id);
            exit;
        }
        
        if (strpos($cb_data, 'ntf_toggle_') === 0 && $state == 'wait_new_task_fields') {
            $field = str_replace('ntf_toggle_', '', $cb_data);
            $step = json_decode($temp, true);
            if (!isset($step['input_fields'])) $step['input_fields'] = [];
            if (in_array($field, $step['input_fields'])) {
                $step['input_fields'] = array_values(array_diff($step['input_fields'], [$field]));
            } else {
                $step['input_fields'][] = $field;
            }
            setState($from_id, 'wait_new_task_fields', json_encode($step));
            
            $all_fields = ['uid' => 'UID', '2fa' => '2FA', 'cookies' => 'Cookies', 'username' => 'Username(auto)', 'email' => 'Email(auto)', 'fb_name' => 'FB Name(auto)', 'password' => 'Password(auto)'];
            $kb = [];
            $row = [];
            foreach ($all_fields as $k => $lbl) {
                $check = in_array($k, $step['input_fields']) ? '✅ ' : '⬜ ';
                $row[] = ['text' => $check . $lbl, 'callback_data' => 'ntf_toggle_' . $k];
                if (count($row) == 2) { $kb[] = $row; $row = []; }
            }
            if (!empty($row)) $kb[] = $row;
            $kb[] = [['text' => '✅ Done', 'callback_data' => 'ntf_done']];
            
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            bot('editMessageReplyMarkup', [
                'chat_id' => $chat_id,
                'message_id' => $msg_id,
                'reply_markup' => json_encode(['inline_keyboard' => $kb])
            ]);
            exit;
        }
        
        if ($cb_data == 'ntf_done' && $state == 'wait_new_task_fields') {
            $step = json_decode($temp, true);
            if (empty($step['input_fields'])) {
                bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '❌ কমপক্ষে একটি ফিল্ড সিলেক্ট করুন', 'show_alert' => true]);
                exit;
            }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            bot('editMessageReplyMarkup', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'reply_markup' => json_encode(['inline_keyboard' => []])]);
            
            if (in_array('uid', $step['input_fields'])) {
                setState($from_id, 'wait_new_task_uid_min', json_encode($step));
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => pe('🆔', '5231012545799666522') . " UID এর সর্বনিম্ন ডিজিট সংখ্যা (default: 10):",
                    'parse_mode' => 'HTML'
                ]);
                exit;
            } else {
                setState($from_id, 'wait_new_task_instruction', json_encode($step));
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => pe('📌', '5397782960512444700') . " ইউজারকে দেখানোর জন্য নির্দেশনা লিখুন (বা <code>skip</code>):",
                    'parse_mode' => 'HTML'
                ]);
                exit;
            }
        }
        
        // পুরোনো কাজের ভিডিও সেট
        if (strpos($cb_data, 'oldtsk_setvideo_') === 0) {
            $id=str_replace('oldtsk_setvideo_','',$cb_data); $task=getManagedTaskById($id); if ($task===null) exit;
            setState($from_id,'wait_old_task_video',json_encode(['id'=>$id,'source'=>'legacy']));
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id]);
            bot('sendMessage',['chat_id'=>$chat_id,'text'=>"🎥 <b>{$task['name']}</b>-এর ভিডিও লিংক দিন।\n\nYouTube/ভিডিওর <b>https://...</b> লিংক দিন। ভিডিও সরাতে <code>skip</code> লিখুন।",'parse_mode'=>'HTML','reply_markup'=>json_encode(['keyboard'=>[[makeBtn('🔙 কাজ ম্যানেজমেন্ট','danger')]],'resize_keyboard'=>true])]); exit;
        }

        // নতুন কাজের ভিডিও সেট
        if (strpos($cb_data, 'tsk_setvideo_') === 0) {
            $id=str_replace('tsk_setvideo_','',$cb_data); $task=getManagedTaskById($id); if ($task===null || ($task['_source']??'custom')==='legacy') exit;
            setState($from_id,'wait_new_task_video',json_encode(['id'=>$id,'source'=>'custom']));
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id]);
            bot('sendMessage',['chat_id'=>$chat_id,'text'=>"🎥 <b>{$task['name']}</b>-এর ভিডিও লিংক দিন।\n\nYouTube/ভিডিওর <b>https://...</b> লিংক দিন। ভিডিও সরাতে <code>skip</code> লিখুন।",'parse_mode'=>'HTML','reply_markup'=>json_encode(['keyboard'=>[[makeBtn('🔙 কাজ ম্যানেজমেন্ট','danger')]],'resize_keyboard'=>true])]); exit;
        }

        // পুরোনো ৫টি কাজের unified management
        if (strpos($cb_data, 'oldtsk_toggle_') === 0) {
            $id=str_replace('oldtsk_toggle_','',$cb_data); $defs=getLegacyTaskDefinitions();
            if (!isset($defs[$id])) exit;
            $key=$defs[$id]['status_key']; $s[$key]=(($s[$key]??'open')==='open')?'closed':'open'; writeDB('settings.json',$s);
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id,'text'=>'✅ আপডেট হয়েছে']);
            $v=legacyTaskDetailView(getManagedTaskById($id),$user_lang); bot('editMessageText',['chat_id'=>$chat_id,'message_id'=>$msg_id,'text'=>$v['text'],'parse_mode'=>'HTML','reply_markup'=>$v['reply_markup']]); exit;
        }
        if (strpos($cb_data, 'oldtsk_edit_') === 0) {
            $rest=str_replace('oldtsk_edit_','',$cb_data); $parts=explode('_',$rest,2); $field=$parts[0]??''; $id=$parts[1]??'';
            $defs=getLegacyTaskDefinitions(); if (!isset($defs[$id]) || !in_array($field,['name','reward','password'])) exit;
            setState($from_id,'wait_old_task_edit',json_encode(['id'=>$id,'field'=>$field]));
            $tcur=getManagedTaskById($id);
            $prompt=$field==='name'?"নতুন নাম লিখুন (বর্তমান: <b>{$tcur['name']}</b>)":($field==='reward'?"নতুন রিওয়ার্ড লিখুন (বর্তমান: <b>{$tcur['reward']}</b>)":"নতুন পাসওয়ার্ড লিখুন (বর্তমান: <code>{$tcur['password']}</code>)");
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id]); bot('sendMessage',['chat_id'=>$chat_id,'text'=>pe('✏️','5253742260054409879').' '.$prompt,'parse_mode'=>'HTML','reply_markup'=>json_encode(['keyboard'=>[[makeBtn('🔙 কাজ ম্যানেজমেন্ট','danger')]],'resize_keyboard'=>true])]); exit;
        }
        if (strpos($cb_data, 'oldtsk_del_') === 0) {
            $id=str_replace('oldtsk_del_','',$cb_data); if (!isset(getLegacyTaskDefinitions()[$id])) exit;
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id]);
            bot('editMessageText',['chat_id'=>$chat_id,'message_id'=>$msg_id,'text'=>'⚠️ <b>'.htmlspecialchars(getManagedTaskById($id)['name']).'</b> ডিলিট করবেন?','parse_mode'=>'HTML','reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>'✅ হ্যাঁ, ডিলিট করুন','callback_data'=>"oldtsk_delconfirm_{$id}"],['text'=>'❌ না, বাতিল','callback_data'=>"oldtsk_delcancel_{$id}"]]]])]); exit;
        }
        if (strpos($cb_data, 'oldtsk_delconfirm_') === 0) {
            $id=str_replace('oldtsk_delconfirm_','',$cb_data); $defs=getLegacyTaskDefinitions(); if (!isset($defs[$id])) exit;
            $s['deleted_'.$id]=1; $s[$defs[$id]['status_key']]='closed'; writeDB('settings.json',$s);
            bot('answerCallbackQuery',['callback_query_id'=>$cb_id,'text'=>'✅ ডিলিট হয়েছে','show_alert'=>true]); bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$msg_id]); showAdminTaskList($chat_id); exit;
        }
        if (strpos($cb_data, 'oldtsk_delcancel_') === 0) { bot('answerCallbackQuery',['callback_query_id'=>$cb_id,'text'=>'বাতিল']); bot('deleteMessage',['chat_id'=>$chat_id,'message_id'=>$msg_id]); showAdminTaskList($chat_id); exit; }

        // unified task view: new/custom + old 5
        if (strpos($cb_data, 'tsk_view_') === 0) {
            $rest=str_replace('tsk_view_','',$cb_data); $parts=explode('_',$rest,2); $src=$parts[0]??'new'; $tid=$parts[1]??'';
            $tcur=getManagedTaskById($tid); bot('answerCallbackQuery',['callback_query_id'=>$cb_id]); if ($tcur===null) exit;
            if ($src==='old') { $view=legacyTaskDetailView($tcur,$user_lang); } else { $view=taskDetailView($tcur,$user_lang); }
            bot('editMessageText',['chat_id'=>$chat_id,'message_id'=>$msg_id,'text'=>$view['text'],'parse_mode'=>'HTML','reply_markup'=>$view['reply_markup']]); exit;
        }
        // পুরোনো code-এর generic tsk_view handler নিচে আর প্রয়োজন নেই
        /*
        if (strpos($cb_data, 'tsk_view_') === 0) {
            $tid = str_replace('tsk_view_', '', $cb_data);
            $tasks = readTasks();
            $tcur = findTaskById($tasks, $tid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($tcur === null) { exit; }
            $view = taskDetailView($tcur, $user_lang);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        
        */
        // টাস্ক টগল
        if (strpos($cb_data, 'tsk_toggle_') === 0) {
            $tid = str_replace('tsk_toggle_', '', $cb_data);
            $tasks = readTasks();
            $idx = findTaskIndex($tasks, $tid);
            if ($idx === -1) { exit; }
            $tasks[$idx]['status'] = (($tasks[$idx]['status'] ?? 'open') == 'open') ? 'closed' : 'open';
            writeTasks($tasks);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ আপডেট হয়েছে']);
            $view = taskDetailView($tasks[$idx], $user_lang);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        
        // টাস্ক এডিট ফিল্ড
        if (strpos($cb_data, 'tsk_editfield_') === 0) {
            $rest = str_replace('tsk_editfield_', '', $cb_data);
            $known = ['uidlimit', 'instruction', 'password', 'emoji', 'reward', 'name'];
            $matched = null;
            $tid = null;
            foreach ($known as $kf) {
                if (strpos($rest, $kf . '_') === 0) {
                    $matched = $kf;
                    $tid = substr($rest, strlen($kf) + 1);
                    break;
                }
            }
            if ($matched === null) { exit; }
            $tasks = readTasks();
            $tcur = findTaskById($tasks, $tid);
            if ($tcur === null) { exit; }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, 'wait_edit_task_value', json_encode(['id' => $tid, 'field' => $matched]));
            
            $prompts = [
                'name' => "নতুন নাম লিখুন (বর্তমান: <b>{$tcur['name']}</b>)",
                'emoji' => "নতুন Emoji পাঠান (বর্তমান: {$tcur['emoji']})",
                'reward' => "নতুন রিওয়ার্ড (৳) (বর্তমান: <b>{$tcur['reward']}</b>)",
                'password' => "নতুন পাসওয়ার্ড (বর্তমান: <code>{$tcur['password']}</code>)",
                'instruction' => "নতুন নির্দেশনা (বর্তমান: " . ($tcur['instruction'] ?: '—') . ")",
                'uidlimit' => "নতুন UID লিমিট ফরম্যাট <code>10-16</code> (বর্তমান: " . ($tcur['uid_min_length'] ?? 10) . "-" . ($tcur['uid_max_length'] ?? 16) . ")"
            ];
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('✏️', '5253742260054409879') . " " . $prompts[$matched],
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['keyboard' => [[makeBtn(L($user_lang, '🔙 বটের সেটিংস', '🔙 Bot Settings'), 'danger')]], 'resize_keyboard' => true])
            ]);
            exit;
        }
        
        // নির্দিষ্ট ইনপুট বাটনের নাম এডিট
        if (strpos($cb_data, 'tsk_editbutton_') === 0) {
            $rest = str_replace('tsk_editbutton_', '', $cb_data);
            $parts = explode('_', $rest, 2);
            $field = $parts[0] ?? '';
            $tid = $parts[1] ?? '';
            if (!in_array($field, ['uid','2fa','cookies'], true) || $tid === '') exit;
            $tasks = readTasks();
            $idx = findTaskIndex($tasks, $tid);
            if ($idx === -1) exit;
            $current = taskFieldButtonLabel($tasks[$idx], $field, $user_lang);
            bot('answerCallbackQuery', ['callback_query_id'=>$cb_id]);
            setState($from_id, 'wait_edit_task_button', json_encode(['id'=>$tid,'field'=>$field]));
            $field_name = strtoupper($field);
            bot('sendMessage', [
                'chat_id'=>$chat_id,
                'text'=>"✏️ <b>{$field_name} বাটনের নতুন নাম লিখুন:</b>\n\nবর্তমান: <code>".htmlspecialchars($current)."</code>",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode(['keyboard'=>[[makeBtn(L($user_lang,'🔙 বটের সেটিংস','🔙 Bot Settings'),'danger')]],'resize_keyboard'=>true])
            ]);
            exit;
        }

        // টাস্ক এডিট ফিল্ডস
        if (strpos($cb_data, 'tsk_editfields_') === 0) {
            $tid = str_replace('tsk_editfields_', '', $cb_data);
            $tasks = readTasks();
            $tcur = findTaskById($tasks, $tid);
            if ($tcur === null) { exit; }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            
            $selected = $tcur['input_fields'] ?? [];
            setState($from_id, 'wait_edit_task_fields', json_encode(['task_id' => $tid, 'selected_fields' => $selected]));
            
            $all_fields = ['uid' => 'UID', '2fa' => '2FA', 'cookies' => 'Cookies', 'username' => 'Username(auto)', 'email' => 'Email(auto)', 'fb_name' => 'FB Name(auto)', 'password' => 'Password(auto)'];
            $kb = [];
            $row = [];
            foreach ($all_fields as $k => $lbl) {
                $check = in_array($k, $selected) ? '✅ ' : '⬜ ';
                $row[] = ['text' => $check . $lbl, 'callback_data' => 'etf_toggle_' . $k];
                if (count($row) == 2) { $kb[] = $row; $row = []; }
            }
            if (!empty($row)) $kb[] = $row;
            $kb[] = [['text' => '✅ Done', 'callback_data' => 'etf_done']];
            
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $msg_id,
                'text' => pe('📝', '5253742260054409879') . " <b>{$tcur['name']}</b> ইনপুট ফিল্ড আপডেট:",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => $kb])
            ]);
            exit;
        }
        
        if (strpos($cb_data, 'etf_toggle_') === 0 && $state == 'wait_edit_task_fields') {
            $field = str_replace('etf_toggle_', '', $cb_data);
            $step = json_decode($temp, true);
            if (!isset($step['selected_fields'])) $step['selected_fields'] = [];
            if (in_array($field, $step['selected_fields'])) {
                $step['selected_fields'] = array_values(array_diff($step['selected_fields'], [$field]));
            } else {
                $step['selected_fields'][] = $field;
            }
            setState($from_id, 'wait_edit_task_fields', json_encode($step));
            
            $all_fields = ['uid' => 'UID', '2fa' => '2FA', 'cookies' => 'Cookies', 'username' => 'Username(auto)', 'email' => 'Email(auto)', 'fb_name' => 'FB Name(auto)', 'password' => 'Password(auto)'];
            $kb = [];
            $row = [];
            foreach ($all_fields as $k => $lbl) {
                $check = in_array($k, $step['selected_fields']) ? '✅ ' : '⬜ ';
                $row[] = ['text' => $check . $lbl, 'callback_data' => 'etf_toggle_' . $k];
                if (count($row) == 2) { $kb[] = $row; $row = []; }
            }
            if (!empty($row)) $kb[] = $row;
            $kb[] = [['text' => '✅ Done', 'callback_data' => 'etf_done']];
            
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            bot('editMessageReplyMarkup', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'reply_markup' => json_encode(['inline_keyboard' => $kb])]);
            exit;
        }
        
        if ($cb_data == 'etf_done' && $state == 'wait_edit_task_fields') {
            $step = json_decode($temp, true);
            if (empty($step['selected_fields'])) {
                bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '❌ কমপক্ষে একটি ফিল্ড সিলেক্ট করুন', 'show_alert' => true]);
                exit;
            }
            $all_tasks = readTasks();
            $idx = findTaskIndex($all_tasks, $step['task_id']);
            if ($idx !== -1) {
                $all_tasks[$idx]['input_fields'] = $step['selected_fields'];
                writeTasks($all_tasks);
            }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ আপডেট হয়েছে']);
            setState($from_id, "");
            
            $tcur = $all_tasks[$idx];
            $view = taskDetailView($tcur, $user_lang);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        
        // টাস্ক ডিলিট
        if (strpos($cb_data, 'tsk_delconfirm_') === 0) {
            $tid = str_replace('tsk_delconfirm_', '', $cb_data);
            $tasks = readTasks();
            $idx = findTaskIndex($tasks, $tid);
            if ($idx === -1) { exit; }
            $deleted_name = $tasks[$idx]['name'];
            array_splice($tasks, $idx, 1);
            writeTasks($tasks);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ ডিলিট', 'show_alert' => true]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('✅', '5206607081334906820') . " কাজ <b>{$deleted_name}</b> ডিলিট হয়েছে।", 'parse_mode' => 'HTML']);
            exit;
        }
        
        if (strpos($cb_data, 'tsk_delcancel_') === 0) {
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => 'বাতিল']);
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminTaskList($chat_id);
            exit;
        }
        
        if (strpos($cb_data, 'tsk_del_') === 0) {
            $tid = str_replace('tsk_del_', '', $cb_data);
            $tasks = readTasks();
            $tcur = findTaskById($tasks, $tid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($tcur === null) { exit; }
            $inline_kb = [[
                ['text' => '✅ হ্যাঁ, ডিলিট করুন', 'callback_data' => "tsk_delconfirm_{$tcur['id']}"],
                ['text' => '❌ না, বাতিল', 'callback_data' => "tsk_delcancel_{$tcur['id']}"]
            ]];
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('⚠️', '5447644880824181073') . " <b>{$tcur['name']}</b> ডিলিট করবেন?", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])]);
            exit;
        }

        // মেথড callbacks
        if ($cb_data == 'list_mm') {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminMethodList($chat_id);
            exit;
        }
        if (strpos($cb_data, 'mm_view_') === 0) {
            $mid = str_replace('mm_view_', '', $cb_data);
            $mm_methods = readMethods();
            $mcur = findMethodById($mm_methods, $mid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($mcur === null) { exit; }
            $view = methodDetailView($mcur);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        if (strpos($cb_data, 'mm_toggle_') === 0) {
            $mid = str_replace('mm_toggle_', '', $cb_data);
            $mm_methods = readMethods();
            $idx = findMethodIndex($mm_methods, $mid);
            if ($idx === -1) { exit; }
            $mm_methods[$idx]['status'] = (($mm_methods[$idx]['status'] ?? 'open') == 'open') ? 'closed' : 'open';
            writeMethodsDB($mm_methods);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅']);
            $view = methodDetailView($mm_methods[$idx]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        if (strpos($cb_data, 'mm_editfield_') === 0) {
            $rest = str_replace('mm_editfield_', '', $cb_data);
            $known_fields = ['field_label', 'name', 'min', 'charge'];
            $matched_field = null;
            $mid = null;
            foreach ($known_fields as $kf) {
                if (strpos($rest, $kf . '_') === 0) {
                    $matched_field = $kf;
                    $mid = substr($rest, strlen($kf) + 1);
                    break;
                }
            }
            if ($matched_field === null) { exit; }
            $mm_methods = readMethods();
            $mcur = findMethodById($mm_methods, $mid);
            if ($mcur === null) { exit; }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, 'wait_edit_method_value', json_encode(['id' => $mid, 'field' => $matched_field]));
            $field_names = ['name' => 'নাম', 'field_label' => 'Field Label', 'min' => 'Min', 'charge' => 'Charge'];
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('✏️', '5253742260054409879') . " <b>{$mcur['name']}</b>-এর নতুন {$field_names[$matched_field]}:",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস', 'danger')]], 'resize_keyboard' => true])
            ]);
            exit;
        }
        if (strpos($cb_data, 'mm_delconfirm_') === 0) {
            $mid = str_replace('mm_delconfirm_', '', $cb_data);
            $mm_methods = readMethods();
            $idx = findMethodIndex($mm_methods, $mid);
            if ($idx === -1) { exit; }
            $deleted_name = $mm_methods[$idx]['name'];
            array_splice($mm_methods, $idx, 1);
            writeMethodsDB($mm_methods);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ ডিলিট', 'show_alert' => true]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('✅', '5206607081334906820') . " মেথড <b>{$deleted_name}</b> ডিলিট।", 'parse_mode' => 'HTML']);
            exit;
        }
        if (strpos($cb_data, 'mm_delcancel_') === 0) {
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => 'বাতিল']);
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminMethodList($chat_id);
            exit;
        }
        if (strpos($cb_data, 'mm_del_') === 0) {
            $mid = str_replace('mm_del_', '', $cb_data);
            $mm_methods = readMethods();
            $mcur = findMethodById($mm_methods, $mid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($mcur === null) { exit; }
            $inline_kb = [[
                ['text' => '✅ হ্যাঁ', 'callback_data' => "mm_delconfirm_{$mcur['id']}"],
                ['text' => '❌ না', 'callback_data' => "mm_delcancel_{$mcur['id']}"]
            ]];
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('⚠️', '5447644880824181073') . " <b>{$mcur['name']}</b> ডিলিট করবেন?", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])]);
            exit;
        }

        // জয়েন চ্যানেল callbacks
        if ($cb_data == 'list_jc') {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminChannelList($chat_id, $s);
            exit;
        }
        if (strpos($cb_data, 'jc_pickcolor_') === 0 && $state == 'wait_add_jc_color') {
            $color_code = str_replace('jc_pickcolor_', '', $cb_data);
            $step_data = json_decode($temp, true);
            if (!is_array($step_data) || empty($step_data['title'])) {
                bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '❌', 'show_alert' => true]);
                setState($from_id, "");
                exit;
            }
            $new_channel = [
                'id' => genChannelId($step_data['title']),
                'title' => $step_data['title'],
                'chat_id' => $step_data['chat_id'],
                'url' => $step_data['url'],
                'color' => colorCodeToEmoji($color_code),
                'status' => 'open',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $jc_list = $s['join_channels'] ?? [];
            $jc_list[] = $new_channel;
            $s['join_channels'] = $jc_list;
            writeDB('settings.json', $s);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, "");
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('✅', '5206607081334906820') . " নতুন চ্যানেল {$new_channel['color']} <b>{$new_channel['title']}</b> যোগ হয়েছে!",
                'parse_mode' => 'HTML',
                'reply_markup' => getJoinChannelManageMenu()
            ]);
            exit;
        }
        if (strpos($cb_data, 'jc_view_') === 0) {
            $jid = str_replace('jc_view_', '', $cb_data);
            $jc_list = $s['join_channels'] ?? [];
            $jcur = findChannelById($jc_list, $jid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($jcur === null) { exit; }
            $view = channelDetailView($jcur);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        if (strpos($cb_data, 'jc_toggle_') === 0) {
            $jid = str_replace('jc_toggle_', '', $cb_data);
            $jc_list = $s['join_channels'] ?? [];
            $idx = findChannelIndex($jc_list, $jid);
            if ($idx === -1) { exit; }
            $jc_list[$idx]['status'] = (($jc_list[$idx]['status'] ?? 'open') == 'open') ? 'closed' : 'open';
            $s['join_channels'] = $jc_list;
            writeDB('settings.json', $s);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅']);
            $view = channelDetailView($jc_list[$idx]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        if (strpos($cb_data, 'jc_editfield_') === 0) {
            $rest = str_replace('jc_editfield_', '', $cb_data);
            $known_fields = ['title', 'chatid', 'url'];
            $matched_field = null;
            $jid = null;
            foreach ($known_fields as $kf) {
                if (strpos($rest, $kf . '_') === 0) {
                    $matched_field = $kf;
                    $jid = substr($rest, strlen($kf) + 1);
                    break;
                }
            }
            if ($matched_field === null) { exit; }
            $jc_list = $s['join_channels'] ?? [];
            $jcur = findChannelById($jc_list, $jid);
            if ($jcur === null) { exit; }
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, 'wait_edit_jc_value', json_encode(['id' => $jid, 'field' => $matched_field]));
            $field_names = ['title' => 'টাইটেল', 'chatid' => 'চ্যানেল আইডি', 'url' => 'লিংক'];
            $current_val = ($matched_field === 'chatid') ? $jcur['chat_id'] : $jcur[$matched_field];
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => pe('✏️', '5253742260054409879') . " <b>{$jcur['title']}</b>-এর নতুন {$field_names[$matched_field]} (বর্তমান: {$current_val}):",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 বটের সেটিংস', 'danger')]], 'resize_keyboard' => true])
            ]);
            exit;
        }
        if (strpos($cb_data, 'jc_colormenu_') === 0) {
            $jid = str_replace('jc_colormenu_', '', $cb_data);
            $jc_list = $s['join_channels'] ?? [];
            $jcur = findChannelById($jc_list, $jid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($jcur === null) { exit; }
            bot('editMessageText', [
                'chat_id' => $chat_id,
                'message_id' => $msg_id,
                'text' => pe('🎨', '5253742260054409879') . " <b>{$jcur['title']}</b>-এর রঙ বেছে নিন:",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode(['inline_keyboard' => colorPickerKeyboard('jc_setcolor', "_{$jcur['id']}")])
            ]);
            exit;
        }
        if (strpos($cb_data, 'jc_setcolor_') === 0) {
            $rest = str_replace('jc_setcolor_', '', $cb_data);
            $known_colors = ['red', 'green', 'blue', 'yellow', 'purple', 'orange'];
            $matched_color = null;
            $jid = null;
            foreach ($known_colors as $kc) {
                if (strpos($rest, $kc . '_') === 0) {
                    $matched_color = $kc;
                    $jid = substr($rest, strlen($kc) + 1);
                    break;
                }
            }
            if ($matched_color === null) { exit; }
            $jc_list = $s['join_channels'] ?? [];
            $idx = findChannelIndex($jc_list, $jid);
            if ($idx === -1) { exit; }
            $jc_list[$idx]['color'] = colorCodeToEmoji($matched_color);
            $s['join_channels'] = $jc_list;
            writeDB('settings.json', $s);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅']);
            $view = channelDetailView($jc_list[$idx]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => $view['text'], 'parse_mode' => 'HTML', 'reply_markup' => $view['reply_markup']]);
            exit;
        }
        if (strpos($cb_data, 'jc_delconfirm_') === 0) {
            $jid = str_replace('jc_delconfirm_', '', $cb_data);
            $jc_list = $s['join_channels'] ?? [];
            $idx = findChannelIndex($jc_list, $jid);
            if ($idx === -1) { exit; }
            $deleted_title = $jc_list[$idx]['title'];
            array_splice($jc_list, $idx, 1);
            $s['join_channels'] = $jc_list;
            writeDB('settings.json', $s);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅', 'show_alert' => true]);
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('✅', '5206607081334906820') . " চ্যানেল <b>{$deleted_title}</b> ডিলিট।", 'parse_mode' => 'HTML']);
            exit;
        }
        if (strpos($cb_data, 'jc_delcancel_') === 0) {
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => 'বাতিল']);
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
            showAdminChannelList($chat_id, $s);
            exit;
        }
        if (strpos($cb_data, 'jc_del_') === 0) {
            $jid = str_replace('jc_del_', '', $cb_data);
            $jc_list = $s['join_channels'] ?? [];
            $jcur = findChannelById($jc_list, $jid);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            if ($jcur === null) { exit; }
            $inline_kb = [[
                ['text' => '✅ হ্যাঁ', 'callback_data' => "jc_delconfirm_{$jcur['id']}"],
                ['text' => '❌ না', 'callback_data' => "jc_delcancel_{$jcur['id']}"]
            ]];
            bot('editMessageText', ['chat_id' => $chat_id, 'message_id' => $msg_id, 'text' => pe('⚠️', '5447644880824181073') . " <b>{$jcur['title']}</b> ডিলিট?", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])]);
            exit;
        }

        // ব্যালেন্স
        if (strpos($cb_data, 'bal_add_') === 0) {
            $target_uid = str_replace('bal_add_', '', $cb_data);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, 'wait_balance_add_amount', $target_uid);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('➕', '5397916757333654639') . " <code>{$target_uid}</code> এ কত টাকা যোগ করবেন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 এডমিন মেনু', 'danger')]], 'resize_keyboard' => true])]);
            exit;
        }
        if (strpos($cb_data, 'bal_sub_') === 0) {
            $target_uid = str_replace('bal_sub_', '', $cb_data);
            bot('answerCallbackQuery', ['callback_query_id' => $cb_id]);
            setState($from_id, 'wait_balance_sub_amount', $target_uid);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => pe('➖', '5447183459602669338') . " <code>{$target_uid}</code> থেকে কত টাকা কাটবেন:", 'parse_mode' => 'HTML', 'reply_markup' => json_encode(['keyboard' => [[makeBtn('🔙 এডমিন মেনু', 'danger')]], 'resize_keyboard' => true])]);
            exit;
        }

        // Withdraw
        if (strpos($cb_data, 'view_wd_') === 0) {
            $wd_id = str_replace('view_wd_', '', $cb_data);
            $withdraws = readDB('withdraws.json');
            foreach ($withdraws as $w) {
                if ($w['id'] == $wd_id) {
                    $method_upper = strtoupper($w['method']);
                    $inline_kb = [
                        [
                            ['text' => '✅ Approve', 'callback_data' => "app_wd_{$w['id']}"],
                            ['text' => '❌ Reject', 'callback_data' => "rej_wd_{$w['id']}"]
                        ],
                        [['text' => '🔙 তালিকা', 'callback_data' => 'list_wd']]
                    ];
                    bot('editMessageText', [
                        'chat_id' => $chat_id,
                        'message_id' => $msg_id,
                        'text' => pe('📥', '5406745015365943482') . " <b>উইথড্র বিস্তারিত:</b>\n\n👤 User: <code>{$w['user_id']}</code>\n📱 মেথড: <b>{$method_upper}</b>\n💳 Account: <code>{$w['account']}</code>\n💰 পরিমাণ: ৳<b>{$w['amount']}</b>",
                        'parse_mode' => 'HTML',
                        'reply_markup' => json_encode(['inline_keyboard' => $inline_kb])
                    ]);
                    exit;
                }
            }
        }
        if (strpos($cb_data, 'app_wd_') === 0) {
            $wd_id = str_replace('app_wd_', '', $cb_data);
            $withdraws = readDB('withdraws.json');
            foreach ($withdraws as $k => $w) {
                if ($w['id'] == $wd_id && $w['status'] == 'pending') {
                    $withdraws[$k]['status'] = 'approved';
                    writeDB('withdraws.json', $withdraws);
                    $users[$w['user_id']]['pending_withdraw'] = max(0, $users[$w['user_id']]['pending_withdraw'] - $w['amount']);
                    $users[$w['user_id']]['total_income'] += $w['amount'];
                    writeDB('users.json', $users);
                    bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '✅ Approved!', 'show_alert' => true]);
                    bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
                    bot('sendMessage', ['chat_id' => $w['user_id'], 'text' => pe('✅', '5206607081334906820') . " <b>পেমেন্ট সফল!</b>\n\n৳<code>{$w['amount']}</code> ({$w['method']}) পাঠানো হয়েছে।", 'parse_mode' => 'HTML']);
                    showAdminWithdrawList($chat_id);
                    exit;
                }
            }
        }
        if (strpos($cb_data, 'rej_wd_') === 0) {
            $wd_id = str_replace('rej_wd_', '', $cb_data);
            $withdraws = readDB('withdraws.json');
            foreach ($withdraws as $k => $w) {
                if ($w['id'] == $wd_id && $w['status'] == 'pending') {
                    $withdraws[$k]['status'] = 'rejected';
                    writeDB('withdraws.json', $withdraws);
                    $users[$w['user_id']]['balance'] += $w['amount'];
                    $users[$w['user_id']]['pending_withdraw'] = max(0, $users[$w['user_id']]['pending_withdraw'] - $w['amount']);
                    writeDB('users.json', $users);
                    bot('answerCallbackQuery', ['callback_query_id' => $cb_id, 'text' => '❌ Rejected!', 'show_alert' => true]);
                    bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $msg_id]);
                    bot('sendMessage', ['chat_id' => $w['user_id'], 'text' => pe('❌', '5210952531676504517') . " আপনার ৳<code>{$w['amount']}</code> উইথড্র রিজেক্ট হয়েছে এবং ব্যালেন্স ফেরত দেওয়া হয়েছে।", 'parse_mode' => 'HTML']);
                    showAdminWithdrawList($chat_id);
                    exit;
                }
            }
        }
    }
}