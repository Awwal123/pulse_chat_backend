<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadImageRequest;
use App\Traits\HttpResponses;
use Cloudinary\Uploader;

class UploadController extends Controller
{
    use HttpResponses;

    public function image(UploadImageRequest $request)
    {
        $uploadedFile = $request->file('image');

        $result = Uploader::upload(
            $uploadedFile->getRealPath(),
            [
                'folder' => 'pulse-chat',
            ]
        );

        return $this->success(
            [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
            ],
            'Image uploaded successfully.'
        );
    }
}