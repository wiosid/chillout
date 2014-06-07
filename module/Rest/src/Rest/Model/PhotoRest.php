<?php

/**
 * Finoit Technologies custom model defined
 * Developer Ramakant Gangwar, Finoit Technologies (gangwar.ramji@gmail.com).
 * 
 * Zend Framework (http://framework.zend.com/)
 *
 */

namespace Rest\Model;

use Swagger\Annotations\Operation;
use Swagger\Annotations\Operations;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Parameters;
use Swagger\Annotations\AllowableValues;
use Swagger\Annotations\Api;
use Swagger\Annotations\ErrorResponse;
use Swagger\Annotations\ErrorResponses;
use Swagger\Annotations\Resource;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;
use Models\Validator\NotInArray as NotInArray;
use Models\Model\PhotosTable;

/**
 * @package
 * @category
 *
 * @Resource(
 *      apiVersion="0.0",
 *      swaggerVersion="1.1",
 *      basePath="http://localhost/stupid-cupid/public/api",
 *      resourcePath="/photo"
 * )
 */
class PhotoRest extends BaseRest {

    protected $photoTable;

    /**
     * Return PhotosTable Model
     *
     * @return PhotosTable
     */
    public function getPhotoTable() {
        return $this->photoTable;
    }

    public function setPhotoTable(PhotosTable $photoTable) {
        $this->photoTable = $photoTable;
    }

    /**
     *
     * @Api(
     *   path="/photos/add",
     *   description="Implemented for uploading photos",
     *   @operations(
     *     @operation(
     *       httpMethod="post",
     *       summary="Implemented for uploading photos",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="add",
     *       @parameters(
     *         @parameter(
     *           name="fld_photo[0]",
     *           description="User photos",
     *           paramType="body",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "file"
     *         ),
     *         @parameter(
     *           name="fld_photo[1]",
     *           description="User photos",
     *           paramType="body",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "file"
     *         ),
     *         @parameter(
     *           name="fld_photo[2]",
     *           description="User photos",
     *           paramType="body",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "file"
     *         ),
     *         @parameter(
     *           name="fld_photo[3]",
     *           description="User photos",
     *           paramType="body",
     *           required= false,
     *           allowMultiple=true,
     *           dataType= "file"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function cAdd($param = Null) {
        //  http://localhost/stupid-cupid/public/api/photos/add
        $fldPhotoArray = $param['fld_photo'];
        $i = 0;
        foreach ($fldPhotoArray as $pKey => $photo) {
            if (isset($photo) && isset($photo['name']) && $photo['name']) {
                $adapter = new \Zend\File\Transfer\Adapter\Http();
                $originalFilename = pathinfo($photo['name']);
                $newProfileFileName = $this->generateUniqueFileName(PHOTO_PATH, 'file-' . str_replace(" ", "_", $originalFilename['filename']), $originalFilename['extension']);
                $adapter->addFilter('Rename', $newProfileFileName);
                $adapter->addValidator('Extension', false, 'jpg', 'jpeg', 'png', 'gif');
                $adapter->setDestination(PHOTO_PATH);
                $extension = strtolower($originalFilename['extension']);
                if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                    if ($adapter->receive($photo['name'])) {
                        $uploadedPhotosArray[] = $newProfileFileName;
                    } else {
                        $error[$i]['key'] = "fld_photo[$pKey]";
                        $error[$i]['code'] = $this->getModel()->getCodeNumber('fileUpload');
                        $error[$i]['code_text'] = "fileUpload";
                        $error[$i]['message'] = "Error in upload.";
                    }
                } else {
                    $error[$i]['key'] = "fld_photo[$pKey]";
                    $error[$i]['code'] = $this->getModel()->getCodeNumber('fileExtension');
                    $error[$i]['code_text'] = "fileExtension";
                    $error[$i]['message'] = "only 'jpg', 'jpeg', 'png', 'gif' extension supported for uploading image.";
                }
                $i++;
            }
        }
        if (!empty($i)) {
            if (empty($error)) {
                foreach ($uploadedPhotosArray as $uploadedPhoto) {
                    $uploadedPhotosIds[] = $this->getPhotoTable()->savePhoto(array('fld_name' => $uploadedPhoto, 'fld_user_id' => $this->getOauth()->getFldUserId()));
                }
                $results['data']['photos'] = $this->getPhotoTable()->fetchPhotos($uploadedPhotosIds);
            } else {
                foreach ($uploadedPhotosArray as $uploadedPhoto) {
                    @unlink(PHOTO_PATH . "/" . $uploadedPhoto);
                }
                $results['errors'] = $error;
            }
        } else {
            $results['errors'] = array(array('key' => 'fld_photo', 'message' => "fld_photo can no be empty", 'code' => $this->getModel()->getCodeNumber(Validator\NotEmpty::IS_EMPTY), 'code_text' => Validator\NotEmpty::IS_EMPTY));
        }
        return $results;
    }

    /**
     *
     * @Api(
     *   path="/photos/delete",
     *   description="Implemented for deleting photos",
     *   @operations(
     *     @operation(
     *       httpMethod="DELETE",
     *       summary="Implemented for deleting photos",
     *       notes="For valid response try valid fld_oauth_token",
     *       responseClass="void",
     *       nickname="delete",
     *       @parameters(
     *         @parameter(
     *           name="fld_photo_id",
     *           description="Photo Id , use comma seperated ids for deleting multiple photos",
     *           paramType="query",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="int"
     *         ),
     *         @parameter(
     *           name="Fld-Oauth-Token",
     *           description="User Oauth Token",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         ),
     *         @parameter(
     *           name="ApiKey",
     *           description="Api Secret Key",
     *           paramType="header",
     *           required=true,
     *           allowMultiple=false,
     *           dataType="string"
     *         )        
     *       )
     *     )
     *   )
     * )
     */
    public function dDelete($param = Null, $query = array()) {
        //  http://localhost/stupid-cupid/public/api/photos/delete
        $user = new Input('fld_photo_id');
        $user->getValidatorChain()->addValidator(new Validator\NotEmpty(), true);

        $inputFilter = new InputFilter();
        $inputFilter->add($user)->setData($query);

        if ($inputFilter->isValid()) {
            $fldPhotoIds = explode(",", $query['fld_photo_id']);
            $i = 0;
            foreach ($fldPhotoIds as $fldPhotoId) {
                if ($row = $this->getPhotoTable()->fetch($fldPhotoId)) {
                    $this->getPhotoTable()->deletePhoto($fldPhotoId);
                    @unlink(PHOTO_PATH . "/" . $row->fld_name);
                    $i++;
                }
            }
            if (!empty($i)) {
                $results['data']['message'] = "Photos deleted successfully.";
            } else {
                $results['errors'] = array(array('key' => 'fld_photo_id', 'message' => 'Invalid fld_photo_id', 'code' => $this->getModel()->getCodeNumber(Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND), 'code_text' => Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND));
            }
        } else {
            $results['errors'] = $this->getModel()->formatValidationErrors($inputFilter->getInvalidInput());
        }
        return $results;
    }

}