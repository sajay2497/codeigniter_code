#############Admin#################
#Autoload me
$autoload['libraries'] = array('database', 'session','form_validation','user_agent','email');

$autoload['helper'] = array('url','form','file','security');
…………………………..


#Controller (Admin)
<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller 
{
	
	public function __construct(){  
		parent::__construct();
		$this->load->model('Admin_Model');  
		
	}
	
	public function checkSession()
	{
		$admin_session=$this->session->userdata('login-in');
		if (!isset($admin_session)) {
			redirect(base_url()."Admin");
		}
	}
	
	public function verify()
	{
		$this->form_validation->set_rules('email','Email','trim|required|valid_email|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE) {
			// $this->load->view('admin/login');
			redirect(base_url()."Admin");
		}else{
			$userDbpass=$this->Admin_Model->getAdminDetail();
			if ($userDbpass) {
				$adminDbpassword=$userDbpass->password;
				$enterPassword=md5($this->input->post('password'));
				if ($adminDbpassword==$enterPassword) {
					$admin_session=array(
						'id'=>$userDbpass->id,
						'name' => $userDbpass->name,
						'email' => $userDbpass->email,
					);
					$this->session->set_userdata('login-in',$admin_session);
					// $sess_data=$this->session->userdata('login-in');
					redirect(base_url()."dashboard");
				}else{ 
					$data['error_message']="Password is Not Valid";
					$this->load->view('admin/login',$data);
				}
			}else{
				$data['error_message']="User Does Not Exists";
				$this->load->view('admin/login',$data);
			}
		}
	}

	public function logout()
	{
		$this->session->set_userdata('login-in');
		$this->session->sess_destroy();
		redirect(base_url()."Admin");
	}
	function do_upload($fileName,$path)
	{
		$config['upload_path'] = './uploads/'.$path;
		$config['allowed_types'] = 'gif|jpg|png|pdf|svg|jpeg';
		$config['max_size']	= '2000';
	//	$config['max_width']  = '160';
	//	$config['max_height']  = '190';
		$this->load->library('upload', $config);
		
		if ( ! $this->upload->do_upload($fileName))
		{
			return $error = array('error' => $this->upload->display_errors(),'upload_data' => "");
		}
		else
		{
			return $data = array('upload_data' => $this->upload->data(),'error' =>"");
		}
	}
	
	public function submitCategory()
	{
		$this->form_validation->set_rules('category','Category','trim|required|xss_clean');
		$this->form_validation->set_rules('meta_tag_title','Meta Tag Title','trim|required|xss_clean');
		$this->form_validation->set_rules('categoryartical','categoryartical','trim|required|xss_clean');
		$this->form_validation->set_rules('meta_tag_description','Meta Tag Description','trim|required|xss_clean');
		$this->form_validation->set_rules('meta_tag_key','Meta Tag Keyword','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE) {
			// $this->load->view('admin/addcategory');
			$data['GetMainCategorys'] = $this->Admin_Model->getCategoryadd();
		$this->load->view('admin/addcategory',$data);
		}else{

			$coverData = $this->do_upload('category_image','img');
			$cover = $coverData['upload_data']['file_name'];

			$this->Admin_Model->categoryadd($cover);
	 		$this->session->set_flashdata('success','Category Saved Successfully');
			redirect(base_url()."Admin/addcategory");
		}
	}
	
	
	####################Model (Admin_Model) #########################
<?php  
defined('BASEPATH') OR exit('No direct script access allowed'); 


class Admin_Model extends CI_Model 
{
	
	public function getAdminDetail()  
	{ 
		$this->db->select('*');
		$this->db->where('email',$this->input->post('email'));
		// $this->db->where('admin_status',1);
		$query=$this->db->get('admin_login'); 
		return $query->row();
	}

	public function urlMaker($string)
	{
		$string = utf8_encode($string);
		$string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);   
		$string = preg_replace('/[^a-z0-9- ]/i', '', $string);
		$string = str_replace(' ', '-', $string);
		$string = trim($string, '-');
		$string = strtolower($string);
		if (empty($string)) {
		return 'n-a';
		}
		return $string.".html";
	 }

	public function categoryadd($cover)
	{
		$this->db->set('category_parent_id',$this->input->post('parentcat'));
		$this->db->set('category_name',$this->input->post('category'));
		$this->db->set('image',$cover);
		$this->db->set('description',$this->input->post('categorydescrption'));
		$this->db->set('category_article',$this->input->post('categoryartical'));
		$this->db->set('meta_title',$this->input->post('meta_tag_title'));
		$this->db->set('meta_desc',$this->input->post('meta_tag_description'));
		$this->db->set('meta_keyword',$this->input->post('meta_tag_key'));
		$this->db->set('category_url',$this->urlMaker($this->input->post('category')));
		$this->db->set('status',$this->input->post('status'));
	    $this->db->insert('category');
	}
	
	public function getCategorys()
	{
		
		$this->db->order_by('id',"desc");
		$query = $this->db->get('category');
		return $query->result();
	}



################################################Front #################################################3
#Controller
Welcome.php

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){ 
		parent::__construct();
		$this->load->model('Front_Model');  
		
	}
	public function index()
	{
		// $data['img_gallery'] = $this->Front_Model->getimg_gallery();
		$data['products'] = $this->Front_Model->getProduct();
		$this->load->view('welcome_message',$data);
	}
	public function checkSession()
	{
		$Logged_in=$this->session->userdata('isUserLogged_in'); 	
 	    if ($Logged_in == 0) { 		
 	        redirect(base_url('welcome/login'));
		}
	}
	public function contact()
	{
		$this->load->view('contact');
	}
	public function account()
	{
		$this->load->view('signup');
	}
	public function AddAccount()
	{
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE) {
				$this->load->view('signup');
			// echo "ajay"; die();

		}else{
			$this->Front_Model->addAccount();
			redirect(base_url()."welcome");
		}
	}
	public function login()
	{
		$this->load->view('login');
	}
	public function getCatData($url)
	{
		$data['catdata']=$this->Front_Model->getCatDatas($url);
		@$catInfo=$data['catdata'];
		$data['meta_title'] =  @$catInfo->meta_title; 
		$data['meta_description'] = @$catInfo->meta_desc;
		$data['meta_keywords'] =  @$catInfo->meta_keyword;
		$data["canonical_url"] = base_url().'category/'.$url;
// extra multipal
		$data['catProduct']=$this->Front_Model->getCatProduct(@$catInfo->id);
		 // print_r($data['catProduct']); die();
		$this->load->view('categorysingle',$data);
	}

	public function getProdData($url)
	{
		$data['proddata']=$this->Front_Model->getProdDatas($url);
		$this->load->view('productdetail',$data);
	}
	public function userlogin()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_rules('email','Email','trim|required|xss_clean');
		$this->form_validation->set_rules('password','Password','trim|required|xss_clean');
 
		if (($this->form_validation->run() == FALSE))
		{
			$this->load->view('login');
		}else{
			$currentUserDetail = $this->Front_Model->getSiteLogin();
			if($currentUserDetail)
			{
			 	$getDbPaas = $currentUserDetail->password;
				$getUserPaas = md5($this->input->post('password'));
                $getbduser = $currentUserDetail->email;	
				$getUsername = $this->input->post('email');
				if( $getUsername == $getbduser &&  $getUserPaas == $getDbPaas)
				{
					$newdata = array(
						'activeUserMail' => $currentUserDetail->email,
						'activeUserId'  => $currentUserDetail->id,
						'isUserLogged_in' => 1);
					$data_user=$this->session->set_userdata($newdata);
					if ($data_user->status== 1) {
						redirect(base_url("Welcome"));
					}else{
						redirect(base_url("Welcome/profile"));
					}
										
				}
				else
				{
					$data['error_message']="Your Password is incorrect!";
					$this->load->view('login',$data);
				}
			}	
			else
			{
				$data['error_message']="We cannot find an account with that email address!";
				// $this->load->view('welcome/login',$data);
				$this->load->view('login',$data);
			}
		}
	
	}
	// login
	// logout
	public function logout()
	{
		
		$removeData = array(
                   'activeUserMail'  => "",
                   'activeUserId'  => "",
                   'isUserLogged_in' => "",
               );
		$this->session->unset_userdata($removeData);
		$this->session->sess_destroy();
		redirect(base_url());
	}

	public function profile()
	{
		$this->checkSession();
		$id = $this->session->userdata('activeUserId');
		$data['user_data']=$this->Front_Model->getUserData($id);
		$this->load->view('profile',$data);
	}

##########session#######

Check session active user #########

$UserLogged_in=$this->session->userdata('isUserLogged_in');

$this->session->userdata('activeUserId');


	
}
