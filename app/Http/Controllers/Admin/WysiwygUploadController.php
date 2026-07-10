<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin-only WYSIWYG upload endpoint.
 *
 * @category Controller
 * @package  App\Http\Controllers\Admin
 * @author   Khairul <khairul@example.com>
 * @license  https://example.local/license Proprietary
 * @link     https://example.local/
 */
class WysiwygUploadController extends Controller
{
    /**
     * Store an uploaded image and return its public URL.
     *
     * @param Request $request Incoming request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        $fileField = $request->hasFile('file') ? 'file' : 'upload';

        $validator = Validator::make(
            $request->all(),
            [
                $fileField => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp,gif',
                    'max:5120',
                ],
            ],
            [
                $fileField.'.required' => 'No file uploaded.',
                $fileField.'.image' => 'The uploaded file must be an image.',
                $fileField.'.mimes' => 'Allowed image types: jpg, jpeg, png, '
                    .'webp, gif.',
                $fileField.'.max' => 'The image may not be greater than 5MB.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                ['error' => ['message' => $validator->errors()->first($fileField)]],
                422
            );
        }

        $path = $request->file($fileField)->store('wysiwyg', 'public');

        $url = asset('storage/'.ltrim($path, '/'));

        return response()->json(
            [
                'location' => $url,
                'url' => $url,
            ]
        );
    }
}
