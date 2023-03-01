<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Upload extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */

     protected $validation;

     public function __construct()
     {
         $this->validation = \Config\Services::validation();
     }
 
    public function index()
    {
        $params = $this->request->getVar();
        $img = $this->request->getFile('userfile');
        $image_name = $img->getName();

        $temp = explode(".", $image_name);
        $newfilename = strtolower(round(microtime(true))) . '.' . end($temp);

        $domain = $params['domain'];
        $folder = $params['folder'];

       
        $show = 'http://localhost/hcm-imageserver/public/show/';

        $ukuran = (array) json_decode($params['size']);
        $ukuran['admin'] = (object) ['width'=>90, 'height'=>90];
   
        $keys = array_keys($ukuran);


       foreach($keys as $row_keys) {
        $ukuran_gambar = trim($ukuran[$row_keys]->width).'x'.trim($ukuran[$row_keys]->height);
        $source_gambar = $show . $ukuran_gambar.'/'.$image_name;
        
        $result[$row_keys] =  $source_gambar;
    //    $link =  $row_keys . '=' .  $source_gambar;
    //    echo '<pre>';
    //    print_r($link);
       }

       //$result['original']    = 'http://localhost/hcm-imageserver/public/show/'

      
        $validationRule = [
            'userfile' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[userfile]',
                    'is_image[userfile]',
                    'mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]'
                ],
                'errors' => 'Image has already been moved'
            ],
            'domain'  => [
                'label' => 'Domain',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Domain cannot be empty'
                ]
             ],
            'folder' => [
                'label' => 'Folder',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Folder cannot be empty'
                ]
                ],
            'size' => [
                'label' => 'Size',
                'rules' => 'required|valid_json',
                'errors' => [
                    'required' => 'Size cannot be empty',
                    'valid_json' => 'Size must contain valid JSON'
                ]
                ],
            'name' => [
                'label' => 'Name',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Name cannot be empty'
                ]
            ]

        ];

        $data_error = []; 

        if (! $this->validate($validationRule)) {
            // $data = ['errors' => $this->validator->getErrors()];
            // $data = ['errors' => 'The file has already been moved.'];
            $data_error['userfile'] = $this->validation->getError('userfile');
            $data_error['domain'] = $this->validation->getError('domain');
            $data_error['folder'] = $this->validation->getError('folder');
            $data_error['size'] = $this->validation->getError('size');
            $data_error['name'] = $this->validation->getError('name');
            // $this->session->setFlashdata($data_error);
            
            
        } else {


            if (!$img->hasMoved()) {
                $ext = $img->getClientExtension();
                $img->move(ROOTPATH . 'public/uploads/', $newfilename);
              
                $data = [
                    'name' => $image_name,
                    'sizes' => json_encode($ukuran),
                    'domain' => $domain,
                    'folder' => $folder,
                    'userfile' => base_url() . "/uploads/"  . $img->getName()
                ];

                $uploadModel = new \App\Models\Upload();
                $uploadModel->simpan_gambar($data);

                $size_key = array_keys($ukuran);

                foreach ($size_key as $row){
                    $a = $ukuran[$row];
                   
                    $width = $a->width;
                    $height = $a->height;
                    $sizes = $width . 'x' . $height;

                    $source = 'uploads/' . $newfilename;
                    $dest = 'resize/' . $domain . '/' . $folder . '/' . $sizes .'-'. $newfilename;
                    $folder_resize = 'resize';
                    $new_path = 'resize/' .$domain . '/' . $folder . '/' . $sizes;
                    

                    if (!is_dir($new_path)) { //create folder if it's not already exixst
                        mkdir($new_path, 0766, true);
                    } else {
                        echo 'Ada'; 
                    }

                    $this->resize($source, $dest, $width, $height);
                }

                //this->resize->source->dest->width->height 
              
            }

             
        }

       

//    if (! $this->validate($validationRule)) {
//     // Jika validasi tidak terpenuhi, tampilkan pesan kesalahan saja
//     $response = [
//         'status'  => 400,
//         'error'   => true,
//         'message' => implode("\n", $data_error)
//     ];
// } else {
//     // Jika validasi terpenuhi, tampilkan result
//     $response = [
//         'status'  => 200,
//         'error'   => false,
//         'data'    => $result        
//     ];
// }




        
        $response = [
            'status'    => 200,
            'error'     => true,
            'message'   => implode("\n", $data_error),
            'data'      => $result
        ];

        return $this->respondCreated($response);

    }

    function resize($source, $dest, $width, $height){
       
        try {
           
            $image = \Config\Services::image()
                ->withFile($source)
                ->fit($width, $height, 'center')   
                ->save($dest);
        } catch (CodeIgniter\Images\Exceptions\ImageException $e) {
            echo $e->getMessage();
        }

        return  $image;
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        //  
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }
}