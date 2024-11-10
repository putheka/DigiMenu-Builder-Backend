<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class UploadMediaController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // Store the file on S3 and make it publicly accessible
            $path = $request->file('file')->store('images', 's3');

            // Set the ACL to public-read for the uploaded file
            Storage::disk('s3')->setVisibility($path, 'public');

            // Get the public URL for the uploaded file
            $url = Storage::disk('s3')->url($path);

            return response()->json([
                'path' => $url, // Return the S3 public URL
                'msg' => 'Upload successful',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
