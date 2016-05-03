<?php
    class pics
    {		
		// Afbeelding resizen
        function resize($source, $stype, $dest, $nw = 200, $nh = 40, $x, $y)
        {
            // Afbeelding en locatie verplicht
            if(empty($source))
            {
                return false;
            }
            if($dest == '')
            {
                return false;
            }

            // Extentie check
            if($stype == 'gif' || $stype == 'jpg' || $stype == 'jpeg' || $stype == 'png')
            {

                // Afbeelding maten
                $size = getimagesize($source);
                $w = $size[0]-$x;
                $h = $size[1]-$y;
				
				$dimg = imagecreatetruecolor($nw, $nh);
				
                // Create from a.d.h.v. extentie
                switch($stype)
                {
                    case 'gif':
                        $simg = imagecreatefromgif($source);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $simg = imagecreatefromjpeg($source);
                        break;
                    case 'png':
                        imagealphablending($dimg, false);
                        imagesavealpha($dimg, true);
                        $simg = imagecreatefrompng($source);
                        imagealphablending($simg, true);
                        break;
                }

                    // Landscape
                    if($w > $h)
                    {
                        $newheight = $h > $nh ? $nh : $h;
                        $newwidth = ($w / $h) * $newheight;
                        $newwidth = $newwidth > $nw ? $nw : $newwidth;
						
                        $dimg = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
                    }
                    // Portrait
                    elseif($w < $h)
                    {
                        $newwidth = $w > $nw ? $nw : $w;
                        $newheight = ($h / $w) * $newwidth;
                        $dimg = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
                    }
                    // Vierkant
                    elseif($w == $h)
                    {
                        $newwidth = $w > $nw ? $nw : $w;
                        $newheight = ($h / $w) * $newwidth;
                        $newheight = $newheight > $nh ? $nh : $newheight;
                        $dimg = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
                    }
                    // Onverklaarbare maat... doe maar wat
                    else
                    {
                        $dimg = imagecreatetruecolor($w, $h);
                        imagecopyresampled($dimg, $simg, 0, 0, 0, 0, $w, $h, $w, $h);
                    }

                // Afbeelding wegschrijven
                switch($stype)
                {
                    case 'gif':
                        imagegif($dimg, $dest);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($dimg, $dest, 100);
                        break;
                    case 'png':
                        imagepng($dimg, $dest, 9);
                        break;
                }
            }
            else
            {
                return false;
            }

            // Gelukt
            return true;
        }
		
		// Thumb
        function thumb($source, $stype, $dest, $nw = 200, $nh = 40, $x, $y, $x2, $y2)
        {
            // Afbeelding en locatie verplicht
            if(empty($source))
            {
                return false;
            }
            if($dest == '')
            {
                return false;
            }

            // Extentie check
            if($stype == 'gif' || $stype == 'jpg' || $stype == 'jpeg' || $stype == 'png')
            {
                // Afbeelding maten
                $size = getimagesize($source);
                $w = $size[0];
                $h = $size[1];
                $dimg = imagecreatetruecolor($nw, $nh);

                // Create from a.d.h.v. extentie
                switch($stype)
                {
                    case 'gif':
                        $simg = imagecreatefromgif($source);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $simg = imagecreatefromjpeg($source);
                        break;
                    case 'png':
                        imagealphablending($dimg, false);
                        imagesavealpha($dimg, true);
                        $simg = imagecreatefrompng($source);
                        imagealphablending($simg, true);
                        break;
                }


				$crop_array = array(
					'x' => $x,
					'y' => $y,
					'width' => $x2,
					'height'=> $y2
				);
				$thumb = imagecrop($simg, $crop_array);

				if($thumb)
				{
					// Afbeelding wegschrijven
					switch($stype)
					{
						case 'gif':
							imagegif($thumb, $dest);
							break;
						case 'jpg':
						case 'jpeg':
							imagejpeg($thumb, $dest, 100);
							break;
						case 'png':
							imagepng($thumb, $dest, 9);
							break;
					}
					
					return true;
				}
            }
			
            return false;
        }
		
		function fill($source, $stype, $dest, $nw = 800, $nh = 600)
        {
            // Afbeelding en locatie verplicht
            if(empty($source))
            {
                return false;
            }
            if($dest == '')
            {
                return false;
            }

            // Extentie check
            if($stype == 'gif' || $stype == 'jpg' || $stype == 'jpeg' || $stype == 'png')
            {

                // Afbeelding maten
                $size = getimagesize($source);
                $w = $size[0];
                $h = $size[1];
                $dimg = imagecreatetruecolor($nw, $nh);

                // Create from a.d.h.v. extentie
                switch($stype)
                {
                    case 'gif':
                        $simg = imagecreatefromgif($source);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $simg = imagecreatefromjpeg($source);
                        break;
                    case 'png':
                        imagealphablending($dimg, false);
                        imagesavealpha($dimg, true);
                        $simg = imagecreatefrompng($source);
                        imagealphablending($simg, true);
                        break;
                }
				
                // Resize
                $new_width = $nw;
                $new_height = round($new_width * ($h / $w));
                $new_x = 0;
                $new_y = round(($nh - $new_height) / 2);
                $next = $new_height < $nh;
                if($next)
                {
                    $new_height = $nh;
                    $new_width = round($new_height * ($w / $h));
                    $new_x = round(($nw - $new_width) / 2);
                    $new_y = 0;
                }
                imagecopyresampled($dimg, $simg, $new_x, $new_y, 0, 0, $new_width, $new_height, $w, $h);

                // Afbeelding wegschrijven
                switch($stype)
                {
                    case 'gif':
                        imagegif($dimg, $dest);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($dimg, $dest, 100);
                        break;
                    case 'png':
                        imagepng($dimg, $dest, 9);
                        break;
                }
            }
            else
            {
                return false;
            }

            // Gelukt
            return true;
        }
    }

?>

