<?php
    /**
     * Created by PhpStorm
     * Date: 17-07-2012
     * Time: 14:27
     * Class for image uploads
     */
    class Upload
    {
        protected $isImage = array(
            'image/png',
            'image/gif',
            'image/jpeg'
        );
        private $name, $type, $tempName, $error, $size, $mimeType, $width, $height;
        private $rootFolder, $uploadFolder, $fieldName, $alternativeName;
        private $maxFileSize, $overwrite, $sequelNumbering, $minWidth, $minHeight;

        /**
         * @param $rootFolder
         * @param $uploadFolder
         * @param $fieldName
         * @param null $alternativeName
         * @param int $maxFileSize
         * @param bool $overwrite
         * @param bool $sequelNumbering
         */
        public function __construct($rootFolder, $uploadFolder, $fieldName, $alternativeName = null, $maxFileSize = 5242880, $overwrite = false, $sequelNumbering = false)
        {
            $this->fieldName = $fieldName;
            $this->maxFileSize = $maxFileSize;
            $this->uploadFolder = $uploadFolder;
            $this->alternativeName = $alternativeName;
            $this->rootFolder = $rootFolder;
            $this->overwrite = $overwrite;
            $this->sequelNumbering = $sequelNumbering;
        }

        /**
         * Upload the file
         * @throws Exception
         * @throws UploadException
         */
        public function upload()
        {
            // Get file data
            $this->fetchUploadFile();

            // Check for errors
            $this->checkFile();

            // Move the uploaded file
            if($this->move() == false)
            {
                error_log('Error attempting to move file');
                throw new Exception('Cannot upload file');
            }
            
            return true;
        }

        /**
         * Get uploaded file data
         */
        protected function fetchUploadFile()
        {
            $fieldName = $this->getFieldName();

            if(is_array($_FILES[$fieldName]['name']) && isset($_FILES[$fieldName]['name'][0]))
            {
                $imagesize = @getimagesize($_FILES[$fieldName]['tmp_name'][0]);
                $this->width = $imagesize[0];
                $this->height = $imagesize[1];
                $this->name = $_FILES[$fieldName]['name'][0];
                $this->type = $_FILES[$fieldName]['type'][0];
                $this->tempName = $_FILES[$fieldName]['tmp_name'][0];
                $this->error = $_FILES[$fieldName]['error'][0];
                $this->size = $_FILES[$fieldName]['size'][0];
                $this->mimeType = $this->fetchMimeType();
            }
            else
            {
                $imagesize = @getimagesize($_FILES[$fieldName]['tmp_name']);
                $this->width = $imagesize[0];
                $this->height = $imagesize[1];
                $this->name = $_FILES[$fieldName]['name'];
                $this->type = $_FILES[$fieldName]['type'];
                $this->tempName = $_FILES[$fieldName]['tmp_name'];
                $this->error = $_FILES[$fieldName]['error'];
                $this->size = $_FILES[$fieldName]['size'];
                $this->mimeType = $this->fetchMimeType();
            }
        }

        /**
         * Check for errors
         */
        protected function checkFile()
        {
            // Check for upload errors
            if($this->error > 0)
            {
                error_log('Error found in checkFile');
                throw new UploadException($this->error);
            }

            // Check if file is an image
            if(!$this->isImage())
            {
                error_log('File is not an image: fails check');
                throw new Exception('Invalid file');
            }

            // Check if file does not exceed maximum size
            if($this->getFileSize() > $this->getMaxFileSize())
            {
                error_log('File exceeds maximum size');
                throw new Exception('File exceeds maximum size');
            }
            elseif($this->getFileSize() == 0){
                error_log('File is empty');
                throw new Exception('File is empty');
            }

            // Check if file is above the minimum dimensions
            if($this->getMinimumWidth() > $this->getWidth() || $this->getMinimumHeight() > $this->getHeight())
            {
                error_log('File dimensions below minimum');
                throw new Exception('File dimensions below minimum: '.$this->getMinimumWidth().'x'.$this->getMinimumHeight().'px');
            }
        }

        /**
         * @return int
         */
        public function getError()
        {
            return (int) $this->error;
        }

        /**
         * @return int
         */
        public function getFileSize()
        {
            return (int)$this->size;
        }

        /**
         * Set minimum width and height of image in pixels
         * Images must be equal or bigger than this to be uploaded
         * @param $width
         * @param $height
         */
        public function setMinimumDimensions($width, $height)
        {
            $this->minWidth = $width;
            $this->minHeight = $height;
        }

        /**
         * Get minimum width in pixels
         * @return int
         */
        public function getMinimumWidth()
        {
            return $this->minWidth;
        }

        /**
         * Get minimum height in pixels
         * @return int
         */
        public function getMinimumHeight()
        {
            return $this->minHeight;
        }

        /**
         * Get width in pixels
         * @return int
         */
        public function getWidth()
        {
            return (int) $this->width;
        }

        /**
         * Get height in pixels
         * @return int
         */
        public function getHeight()
        {
            return (int) $this->height;
        }

        /**
         * @return string
         */
        protected function fetchMimeType()
        {

            if(is_file($this->tempName) == false)
            {
                return '';
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $this->mimeType = $mimeType = finfo_file($finfo, $this->tempName);
            finfo_close($finfo);

            return $mimeType;
        }

        /**
         * @return mixed
         */
        public function getMimeType()
        {
            return $this->mimeType;
        }

        /**
         * @param $name
         * @return mixed
         */
        protected function getExtension($name)
        {
            $pathinfo = pathinfo($name);

            return $pathinfo['extension'];
        }

        /**
         * @return string
         */
        public function getFileName()
        {
            return ($this->getAlternativeName()) ? $this->getAlternativeName().'.'.$this->getExtension($this->name) : $this->name;
        }

        /**
         * @param mixed $fieldName
         */
        public function setFieldName($fieldName)
        {
            $this->fieldName = $fieldName;
        }

        /**
         * @return mixed
         */
        public function getFieldName()
        {
            return $this->fieldName;
        }

        /**
         * @param null $alternativeName
         */
        public function setAlternativeName($alternativeName)
        {
            $this->alternativeName = $alternativeName;
        }

        /**
         * @return mixed
         */
        public function getAlternativeName()
        {
            return $this->alternativeName;
        }

        /**
         * @param int $maxFileSize
         */
        public function setMaxFileSize($maxFileSize)
        {
            $this->maxFileSize = $maxFileSize;
        }

        /**
         * @return mixed
         */
        public function getMaxFileSize()
        {
            return $this->maxFileSize;
        }

        /**
         * @param mixed $rootFolder
         */
        public function setRootFolder($rootFolder)
        {
            $this->rootFolder = $rootFolder;
        }
        /**
         * @return mixed
         */
        public function getRootFolder()
        {
            return rtrim($this->rootFolder, '/').'/';
        }

        /**
         * @param mixed $uploadFolder
         */
        public function setUploadFolder($uploadFolder)
        {
            $this->uploadFolder = $uploadFolder;
        }

        /**
         * @return mixed
         */
        public function getUploadFolder()
        {
            return $this->uploadFolder;
        }

        /**
         * @param boolean $overwrite
         */
        public function setOverwrite($overwrite)
        {
            $this->overwrite = (bool)$overwrite;
        }

        /**
         * @return boolean
         */
        public function getOverwrite()
        {
            return $this->overwrite;
        }

        /**
         * @param boolean $sequelNumbering
         */
        public function setSequelNumbering($sequelNumbering)
        {
            $this->sequelNumbering = (bool)$sequelNumbering;
        }

        /**
         * @return boolean
         */
        public function getSequelNumbering()
        {
            return $this->sequelNumbering;
        }

        /**
         * @return bool
         */
        public function isImage()
        {
            return in_array($this->fetchMimeType(), $this->isImage);
        }

        /**
         * @param $mimeType
         * @return bool
         */
        public function isMimeType($mimeType)
        {
            if(is_array($mimeType))
            {
                if(!in_array($this->fetchMimeType(), $mimeType))
                {
                    error_log('File type not accepted ('.$this->fetchMimeType().')');

                    return false;
                }

                return true;
            }

            return ($this->fetchMimeType() == $mimeType);
        }

        /**
         * @return bool
         */
        protected function createFolderWhenNotExists()
        {
            if(is_dir($this->getUploadFolder()) == false)
            {

                // Create folder
                if(mkdir($this->getUploadFolder(), 0777, true) === false)
                {
                    error_log('Unable to create folder');
                    return false;
                }

                // Update access rights
                $this->updateAccessRights();
            }

            return true;
        }

        /**
         * @return bool
         */
        public function updateAccessRights()
        {
            $folders = explode('/', str_replace($this->getRootFolder(), '', $this->getUploadFolder()));
            $folder2 = $this->getRootFolder();
            $folder2 .= array_shift($folders).'/';
            foreach($folders as $folder)
            {
                if(empty($folder) === false)
                {
                    $folder2 .= $folder.'/';
                    if(chmod($folder2, 0777) === false)
                    {
                        error_log('Unable to chmod folder. ('.$folder2.')');
                        return false;
                    }
                }
            }

            return true;
        }

        /**
         * @return bool
         */
        public function move()
        {
            if($this->createFolderWhenNotExists())
            {
				// Create valid and unique file name
                $fileName = $this->name;

                if($this->getSequelNumbering())
                {
                    $this->name = $fileName = $this->file_newname($this->getUploadFolder(), $fileName);
                    $destination = $this->getUploadFolder().$fileName;
                }
                else
                {
                    $destination = $this->getUploadFolder().$fileName;
                    if(file_exists($destination) && $this->getOverwrite() === false)
                    {
                        error_log('Duplicate file. ('.$destination.')');
                        return false;
                    }
                }

                return move_uploaded_file($this->tempName, $destination);
            }
            else
            {
                error_log('Unable to create upload folder. ('.$this->getUploadFolder().')');
                return false;
            }
        }

        /**
         * @param $path
         * @param $filename
         * @return string
         */
        protected function file_newname($path, $filename)
        {
            if($pos = strrpos($filename, '.'))
            {
                $name = substr($filename, 0, $pos);
                $ext = substr($filename, $pos);
            }
            else
            {
                $name = $filename;
            }

            $newpath = $path.'/'.$filename;
            $newname = $filename;
            $counter = 0;
            while(file_exists($newpath))
            {
                $newname = $name.'_'.$counter.$ext;
                $newpath = $path.'/'.$newname;
                $counter++;
            }

            return $newname;
        }
    }

    /**
     * Class UploadException
     */
    class UploadException extends Exception
    {
        /**
         * @param $code
         */
        public function __construct($code)
        {
            $message = $this->codeToMessage($code);
            parent::__construct($message, $code);
        }

        /**
         * @param $code
         * @return string
         */
        private function codeToMessage($code)
        {
            switch($code)
            {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'Image size too big';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'Upload error: partial';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'Invalid file';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = 'Upload error: no tmp dir';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = 'Upload error: cannot write';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = 'Upload error: extension';
                    break;
                default:
                    $message = 'Upload error';
                    break;
            }
            return $message;
        }
    }