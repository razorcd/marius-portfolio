<?php  
	header('Cache-control: no-cache');
	session_start();
	
	// Keep lowercase
	$username = 'marius_username_here';
	$pwhash = 'password_hash_here';
	
	/*
		How-to create a new password:
		password_hash('new_password');
		Above function will give a hash, assign it to $pwhash
		! Change TOKEN too
	*/
	
	// Login
	if(isset($_POST['username']) && isset($_POST['password']))
    {
		if(trim($_POST['username']) === strtolower($username) && password_verify($_POST['password'], $pwhash))
		{
			$_SESSION['user'] = $username;
		}
		else
		{
			echo 'Invalid username or password';
		}
	}
	
	// Logout
	if(isset($_POST['logout']) && $_SESSION['user'])
	{
		unset($_SESSION['user']);
		unset($_SESSION['type']);
		session_destroy();
	}

	// Only do this if logged in
	if(isset($_SESSION['user']) && $_SESSION['user'] == $username)
	{
		// Default type
		$type = 'illustration';
		// Possible types
		$types = array(
			'illustration',
			'characters',
			'environments',
			'personal'
		);
		
		// If type change and type exists
		if(isset($_GET['type']) && in_array($_GET['type'], $types))
		{
			// Set type session
			$_SESSION['type'] = $_GET['type'];
		}
		
		// If type session exists, use it
		if(isset($_SESSION['type']))
		{
			$type = $_SESSION['type'];
		}
		
		// Folders
		$rootFolder = __DIR__.'/';
		$uploadFolder = '../portfolio/'.$type.'/';
		$json_location = '../portfolio.json';
		
		// Upload data
		$time = time();
		$token = 'token_here';
		$uploadToken = md5('token_here'.$time);
		
		// Thumbnail sizes
		$thumbWidth = 200;
		$thumbHeight = 40;

		// Load classes
		include('pics.php');
		include('upload.php');
		
		// Upload images
		if(isset($_POST['timestamp']) && isset($_POST['token']))
		{
			$posttoken = md5($token.$_POST['timestamp']);
			if(empty($_FILES) == false && $_POST['token'] == $posttoken)
			{
				try
				{
					// Upload the image, create new instance
					$upload = new Upload($rootFolder, $uploadFolder, 'file');
					// Create new name if name is duplicate
					$upload->setSequelNumbering(true);
					$upload->setMaxFileSize(1000000);
					$upload->upload();

					// Filename
					$filename = $upload->getFileName();
					// Extension
					$explosion = explode('.', strtolower($filename));
					$ext = end($explosion);
					// Filename for thumbnail
					$thumbFileName = $explosion[0].'_Thumb.'.$ext;
					// Create a thumbnail
					pics::fill($uploadFolder.$filename, $ext, $uploadFolder.$thumbFileName, $thumbWidth, $thumbHeight);
					
					// Get current image data
					$json_file = file_get_contents($json_location, true);
					// Convert json to array
					$json = json_decode($json_file, true);

					// Get last key of image array
					$newkeys = array();
					$keys = array_keys($json[$type]);
					foreach($keys as $key)
					{
						// Ditch the 'img' so we get the highest numeric value
						$newkeys[] = substr($key, 3);
					}
					// Create a new key
					$newkey = 'img'.(max($newkeys)+1);
					// Add new upload data to array
					$json[$type][$newkey] = array(
						'url' => 'portfolio/'.$type.'/'.$filename,
						'thumb' => 'portfolio/'.$type.'/'.$thumbFileName
					);

					// Write the json file
					file_put_contents($json_location, json_encode($json, JSON_PRETTY_PRINT));

					// Send file data back to client
					echo json_encode(array(
						'status' => 'success',
						'key'	 => $newkey,
						'thumb'  => $thumbFileName,
						'url'    => $filename
					));
				}
				catch(Exception $e)
				{
					// Set error header
					header("HTTP/1.0 415 Unsupported Media Type");
					// Set json header
					header('Content-Type: application/json');
					// Return error
					echo json_encode(array(
						'error' => $e->getMessage()
					));
				}
			}
			else
			{
				// Set error header
				header("HTTP/1.0 415 Unsupported Media Type");
				// Set json header
				header('Content-Type: application/json');
				// Return error
				echo json_encode(array(
					'error' => 'Invalid upload.'
				));
			}
			
			// Die here, POSTs should not load the page
			die;
		}
		
		// Sort images
		if(isset($_POST['images']) && isset($_POST['type']))
		{
			// Get current image data
			$json_file = file_get_contents($json_location, true);
			// Convert json to array
			$json = json_decode($json_file, true);

			// Die if post value is not an array
			$images = is_array($_POST['images']) ? $_POST['images'] : die;
			
			// Only continue if this type is defined
			if(in_array($_POST['type'], $types))
			{
				// Save type in variable for easier access
				$type = $_POST['type'];
				// Create array for holding images
				$newArray = array();
				// Counter
				$i = 0;
				
				// Loop through current type images
				foreach($json[$type] as $key => $values) 
				{
					// Add the images to the new array in selected order
					$newArray[$images[$i]] = $json[$type][$images[$i]];
					$i++;
				}
				
				// Add new order to array
				$json[$type] = $newArray;

				// Write the json file
				file_put_contents($json_location, json_encode($json, JSON_PRETTY_PRINT));
			}
			die;
		}
		
		// Delete
		if(isset($_POST['image']) && isset($_POST['type']))
		{
			// Get current image data
			$json_file = file_get_contents($json_location, true);
			// Convert json to array
			$json = json_decode($json_file, true);

			// Remove any slashes from post value
			$image = str_replace('/','', stripslashes($_POST['image']));
			
			// Only continue if this type is defined
			if(in_array($_POST['type'], $types))
			{
				// Save type in variable for easier access
				$type = $_POST['type'];

				// Delete the file and the thumbnail
				if(unlink('../'.$json[$type][$image]['url']) && unlink('../'.$json[$type][$image]['thumb']))
				{
					// Remove selected element from array
					unset($json[$type][$image]);
					
					// Write the json file
					file_put_contents($json_location, json_encode($json, JSON_PRETTY_PRINT));
					
					// Send file data back to client
					echo json_encode(array(
						'status' => 'success',
						'key'	=> $image,
					));
					die;
				}
			}
			
			// Delete failed, return status
			echo json_encode(array(
				'status' => 'error'
			));
			die;
		}
		
		// Edit thumbnail
		if(isset($_POST['thumb']))
		{
			// Required values
			$thumbnailData = array(
				'x1',
				'y1',
				'x2',
				'y2',
			);
			$continue = true;
			
			// Check post data
			foreach($thumbnailData as $data)
			{
				if(!isset($_POST[$data]) || !is_numeric($_POST[$data]))
				{
					$continue = false;
				}
			}
			
			if($continue)
			{
				// Calcuate submitted post values
				$x = $_POST['x2'] - $_POST['x1']; 
				$y = $_POST['y2'] - $_POST['y1'];
				
				// Check minimum size
				if($x >= $thumbWidth && $y >= $thumbHeight)
				{
					// Get current image data
					$json_file = file_get_contents($json_location, true);
					// Convert json to array
					$json = json_decode($json_file, true);
					
					$image = $json[$type][$_POST['thumb']]['url'];
					if(file_exists('../'.$image))
					{
						// Extension
						$explosion = explode('.', strtolower($image));
						$ext = end($explosion);
						
						// Filename for thumbnail
						$thumbFileName = $explosion[0].'_Thumb.'.$ext;

						// Create a thumbnail
						$result = pics::thumb('../'.$image, $ext, '../'.$thumbFileName, $thumbWidth, $thumbHeight, $_POST['x1'], $_POST['y1'], $_POST['x2'], $_POST['y2']);
						
						if($result)
						{
							// Resize image
							pics::resize('../'.$thumbFileName, $ext, '../'.$thumbFileName, $thumbWidth, $thumbHeight, $_POST['x1'], $_POST['y1']);

							if($result)
							{
								// Send success status back to client
								echo json_encode(array(
									'status' => 'success',
									'url' => '../'.$json[$type][$_POST['thumb']]['url'],
									'thumb' => '../'.$json[$type][$_POST['thumb']]['thumb']
								));
								die;
							}
							else
							{
								$error = 'Could not resize thumbnail to correct size';
							}
						}
						else
						{
							$error = 'Could not create thumbnail';
						}
					}
					else
					{
						$error = 'This image does not exist';
					}
				}
				else
				{
					$error = 'Size too small, select a larger area and try again';
				}
			}
			else
			{
				$error = 'Missing required data';
			}
			
			// Send fail status back to client
			echo json_encode(array(
				'status' => 'error',
				'error' => $error
			));
			die;
		}	
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Image uploader</title>

		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/imgareaselect.css">
		<link rel="stylesheet" href="css/jquery-ui.structure.min.css">
		<link rel="stylesheet" href="css/jquery-ui.theme.min.css">
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
					</button>
					<?php
						// List image types when logged in
						if(isset($_SESSION['user']) && $_SESSION['user'] == $username)
						{
							foreach($types as $typ)
							{
								echo '<a class="navbar-brand '.($type == $typ ? 'active' : '').'" href="?type='.$typ.'">'.ucfirst($typ).'</a>';
							}
						}
					?>
				</div>
				<?php
					// Change header when logged in
					if(isset($_SESSION['user']) && $_SESSION['user'] == $username)
					{
						?>
							<div id="navbar" class="navbar-collapse collapse">
								<form class="navbar-form navbar-right" action="" method="post">
									<input type="hidden" name="logout" value="1" />	
									<button type="submit" class="btn btn-info">Log out</button>
								</form>
							</div>
						<?php
					}
					else
					{
						?>
							<div id="navbar" class="navbar-collapse collapse">
								<form class="navbar-form navbar-right" action="" method="post">
									<div class="form-group">
										<input type="text" placeholder="Username" name="username" class="form-control">
									</div>
									<div class="form-group">
										<input type="password" placeholder="Password" name="password" class="form-control">
									</div>
									<button type="submit" class="btn btn-info">Sign in</button>
								</form>
							</div>
						<?php
					}
				?>
			</div>
		</nav>

		<?php
			// Only do this when logged in
			if(isset($_SESSION['user']) && $_SESSION['user'] == $username)
			{
				?>
					<div class="container">

						<div class="col-md-12">
							<form action="" method="post" class="form drop">
				
								<div class="dz-message needsclick">Drop files here or click to upload.</div>
								<input type="hidden" name="token" value="<?=$uploadToken?>">
								<input type="hidden" name="timestamp" value="<?=$time?>">

							</form>
						</div>

						<div id="sortable">
							<div class="table-striped" class="files " id="previews">
								<div id="template" class="file-row clearfix col-md-3">						
									<div class='previewWrap'>
										<span class="preview"><img data-dz-thumbnail /></span>
									</div>
									<div class='content'>
										<div class='pull-right actions'>					         
										  <button data-dz-remove class="btn btn-xs btn-danger delete">
											<i class="fa fa-trash-o"></i>
											<span>delete</span>
										  </button>
										</div>
										<p class="name" data-dz-name></p>
										<p class="size" data-dz-size></p>
										<strong class="error text-danger" data-dz-errormessage></strong>
									</div>
									<div class='clearfix'>
										<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
											<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
										</div>
									</div>
								   
								</div>
							</div>
							
							<?php
								// Get image data
								$json_file = file_get_contents($json_location, true);
								// Convert json to array
								$json = json_decode($json_file, true);
								foreach($json[$type] as $key => $file)
								{
									if(file_exists('../'.$file['url']) && file_exists('../'.$file['thumb']))
									{
										echo '
											<div class="col-md-3 image">
												<span class="plus" title="Edit thumbnail" data-img="../'.$file['url'].'">&#9633;</span><img id="'.$key.'" src="../'.$file['thumb'].'"/><span title="Delete image" class="x">X</span>
											</div>
										';
									}
								}
							?>
							
						</div>
					</div>	

					<script src="js/dropzone.js"></script>
					<script src="js/jquery-1.9.1.min.js"></script>
					<script src="js/jquery-ui.min.js"></script>
					<script src="js/bootstrap.min.js"></script>
					<script src="js/jquery.imgareaselect.js"></script>
					<script>
						var previewNode = document.querySelector('#template');
						previewNode.id = "";
						var previewTemplate = previewNode.parentNode.innerHTML;
						previewNode.parentNode.removeChild(previewNode);

						var myDropzone = new Dropzone('.drop',
						{
							url: "index.php",
							previewTemplate: previewTemplate,
							previewsContainer: '#previews',
							thumbnailWidth: <?=$thumbWidth?>,
							thumbnailHeight: <?=$thumbHeight?>,
						});
						
						myDropzone.on('success', function(file, response)
						{
							response = JSON.parse(response);
							if(file.status == 'success')
							{
								// Add thumbnail
								$('#sortable').append('<div class="col-md-3 image"><span class="plus" title="Edit thumbnail" data-img="<?=$uploadFolder?>'+response.url+'">&#9633;</span><img id="'+response.key+'" src="<?=$uploadFolder?>'+response.thumb+'"/><span class="x" title="Delete image">X</span></div>');
								// Remove success preview
								$('#previews .dz-success').remove();
							}
						});
					
						Dropzone.options.myAwesomeDropzone = {
							paramName: "file", // The name that will be used to transfer the file
							maxFilesize: 5, // MB
						};
						
						$(function()
						{
							// Sort the image
							$('#sortable').sortable({
								update: function(event, ui)
								{
									var images = [];
									
									$('.image img').each(function(i)
									{
										images[i] = $(this).attr('id');
									});

									// Post changes to server
									$.post('index.php', 
									{
										type: '<?=$type?>',
										images: images
									});
								}
							});
							$('#draggable').draggable({
								connectToSortable: '#sortable',
							});
							$('#sortable div img').disableSelection();
							
							// Delete image function
							$('#sortable').on('click', 'span.x', function()
							{
								if(!confirm('Are you sure you want to delete this image?'))
								{
									return false;
								}
								$.post('index.php', 
								{
									type: '<?=$type?>',
									image: $(this).parent().find('img').attr('id')
								},
								function(data)
								{
									// Convert data to js object
									response = JSON.parse(data);
									// Check response
									if(response.status == 'success')
									{
										// Image deleted, remove thumbnail
										$('#'+response.key).parent().remove();
									}
									else
									{
										alert('Could not delete this image');
									}
								});
							});
							
							$('#dialog').dialog({
								width:'auto',
								height: 'auto',
								autoOpen: false,
								modal: true,
							});
							
							var h = $(window).height();
							var w = $(window).width();
							var boxHeight = $('.ui-dialog').height();
							var boxWidth = $('.ui-dialog').width();

							$('.ui-dialog').css({
								'left': ((w - boxWidth)/2)/2,
								'top' : (((h - boxHeight)/2)/2)-150,
								'z-index' : 99999
							});
							
							$('#sortable').on('click', 'span.plus', function()
							{							
								$('#dialog').text('');
								var html = `
									<span id="xy"">
										<input type="hidden" name="x1" value="" />
										<input type="hidden" name="y1" value="" />
										<input type="hidden" name="x2" value="" />
										<input type="hidden" name="y2" value="" />
									</span>
									<button type="button" id="editThumb" class="btn btn-info">Submit</button><br><br>
								`;
								$('#dialog').append(html + '<img id="thumbImg" data-id="'+$(this).parent().find('img').attr('id')+'" src="'+$(this).data('img')+'" />');
								$('#dialog').dialog('open');
			
								$('.ui-dialog').css({
									'left': ((w - boxWidth)/2)/2,
									'top' : (((h - boxHeight)/2)/2)-150,
									'z-index' : 99999
								});
								
								$('#dialog img').imgAreaSelect({
									x1: 0,
									y1: 0,
									x2: 200,
									y2: 40,
									handles: true,
									parent: '#dialog',
									aspectRatio: '200:40',
									onSelectEnd: function (img, selection) {
										$('input[name="x1"]').val(selection.x1);
										$('input[name="y1"]').val(selection.y1);
										$('input[name="x2"]').val(selection.x2);
										$('input[name="y2"]').val(selection.y2);            
									}
								});
							});
					
							$('#dialog').on('click', 'button#editThumb', function()
							{
								var id = $('#thumbImg').data('id');
								var parent = $('#'+id).parent().find('img');
								var src = parent.attr('src');
								$.post('index.php', 
								{
									x1: $('input[name="x1"]').val(),
									y1: $('input[name="y1"]').val(),
									x2: $('input[name="x2"]').val(),
									y2: $('input[name="y2"]').val(),
									thumb: id
								},
								function(data)
								{
									// Convert data to js object
									response = JSON.parse(data);
									// Check response
									if(response.status == 'success')
									{
										parent.attr('src', src + '?t='+new Date().getTime());
										alert('Thumbnail saved');
									}
									else
									{
										alert(response.error);
									}
								});
							});
						});
					</script>
					<div id="dialog" title="Image thumbnail" style="display:none">
						
					</div>
				<?php 
			}
		?>
	</body>
</html>