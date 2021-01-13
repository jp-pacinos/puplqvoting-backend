<?php

namespace App\Http\Controllers\Features\Admin\Parties;

use App\Models\Official;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class OfficialPictureController extends Controller
{
    public function store(Request $request, Official $official)
    {
        $request->validate([
            'official_image' => 'required|image',
        ]);

        if (! $request->hasFile('official_image')) {
            return response()->json(['message' => 'No file found.', 'image_url' => null]);
        }

        $imagePath = $this->storeImage($request->official_image, $official->id);

        $official->update([
            'display_picture' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Photo uploaded.',
            'image_url' => config('app.url').Storage::url($imagePath),
        ], 201);
    }

    public function destroy(Official $official)
    {
        if ($official->getRawOriginal('display_picture')) {
            Storage::disk('public')->delete($official->getRawOriginal('display_picture'));
        }

        $status = $official->update([
            'display_picture' => null,
        ]);

        return response()->json(['message' => 'Photo deleted.', 'success' => $status]);
    }

    private function storeImage($imageFile, $filename)
    {
        $image = Image::make($imageFile);

        $image->resize(260, 315, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $filePath = $this->resolveNameAndLocation($filename).'.jpg';

        Storage::disk('public')->put($filePath, $image->encode('jpg'));

        return $filePath;
    }

    private function resolveNameAndLocation($filename)
    {
        return 'images/official-'.$filename;
    }
}
