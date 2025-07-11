<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlaceholderController extends Controller
{
    public function placeholder(Request $request)
    {
        $width = $request->query('width', 100);
        $height = $request->query('height', 100);
        $text = $request->query('text', 'CyberEd');
        
        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 15, 23, 42);
        imagefill($image, 0, 0, $bgColor);
     

        $textColor = imagecolorallocate($image, 255, 255, 255);
  
        $fontSize = min($width, $height) / 10;
        
        $fontFile = 5; 
        $textWidth = imagefontwidth($fontFile) * strlen($text);
        $textHeight = imagefontheight($fontFile);
        
  
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
   
        imagestring($image, $fontFile, $x, $y, $text, $textColor);
        

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}