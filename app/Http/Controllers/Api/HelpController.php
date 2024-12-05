<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ResponseFormatter;
use App\Models\About;
use App\Models\Contact;
use App\Models\Privacy;
use App\Models\Quest;
use App\Models\QuestCategory;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function category()
    {
        $abouts = QuestCategory::where('is_active', true)->get();

        return ResponseFormatter::success($abouts, 'Successfullt get FAQ category!');
    }

    public function faq(Request $request)
    {
        $abouts = Quest::query()
            ->when($request->category, function ($query, $category) {
                $query->where('category_id', $category);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get();

        return ResponseFormatter::success($abouts, 'Successfully retrieved FAQs!');
    }

    public function contact()
    {
        $contacts = Contact::get();

        return ResponseFormatter::success($contacts, 'Successfully retrieved Contacts!');
    }

    public function privacy()
    {
        $privacy = Privacy::first();

        return ResponseFormatter::success($privacy, 'Successfully retrieved Privacy!');
    }

    public function abouts()
    {
        $abouts = About::get();

        return ResponseFormatter::success($abouts, 'Successfully retrieved Abouts!');
    }
}
