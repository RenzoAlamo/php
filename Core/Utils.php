<?php

namespace Core;

class Utils
{

  /**
   * If the file is uploaded, use move_uploaded_file, otherwise use rename
   * 
   * @param string tmp_name The temporary filename of the file in which the uploaded file was stored on the server.
   * @param string destination The path to the file where you want to save the uploaded file.
   * 
   * @return The return value is a boolean in case the file was successfully uploaded.
   */
  public static function uploadFile($tmp_name, $destination)
  {
    if (is_uploaded_file($tmp_name)) {
      return move_uploaded_file($tmp_name, $destination);
    } else {
      return rename($tmp_name, $destination);
    }
  }

  public static function isAJAXrequest()
  {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') || $_SERVER["HTTP_SEC_FETCH_SITE"] !== "none";
  }
}
