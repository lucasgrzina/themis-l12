<?php
//app/Helpers/Envato/User.php
namespace App\Helpers;
use Illuminate\Support\Facades\Storage;
 
class FileUploadHelper {

    public static function path($dirPath='',$disk = 'uploads')
    {
    	return Storage::disk($disk)->path($dirPath);
    }

	public static function fullUrl($dirPath='',$filename='',$disk = 'uploads')
	{
		return asset(Storage::disk($disk)->url($dirPath.'/'.$filename));
	}    
}