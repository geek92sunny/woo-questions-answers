	<?php  

if ( ! defined( 'ABSPATH' ) ) exit;


class ETS_WOO_PRODUCT_USER_QUESTION_ANSWER 
{		
	public function __construct() {
	 
		// Create the new Tabe Add Question Field
		add_filter( 'woocommerce_product_tabs',  		 	array($this, 'ets_question_tab'));
		
		add_action( 'wp_ajax_ets_post_qusetion_answer',		array($this, 'ets_post_qusetion_answer'));	

		// Load The Q & A on click Load More Button
		add_action( 'wp_ajax_ets_product_qa_load_more',		array($this, 'ets_product_qa_load_more'));

		// without login
		add_action( 'wp_ajax_nopriv_ets_product_qa_load_more',		array($this, 'ets_product_qa_load_more'));

		//variable Creation js
		add_action( 'wp_enqueue_scripts',array($this, 'ets_woo_qa_scripts' ));

		//Add CSS file
		add_action( 'wp_enqueue_scripts',array($this, 'ets_woo_qa_style'));	 

		//SMTP mail Hook
		add_action('phpmailer_init',array($this, 'configure_smtp') );

		//Mail Content Type Html
		add_filter( 'wp_mail_content_type',array($this, 'ets_set_html_content_type'));


	}

	/**
	*Create the new Tabe Add Question Field
	*/ 
	public function ets_question_tab( $tabs ) { 
	     
	    $tabs['ask'] = array(
		    'title'     =>  __( 'Ask me', 'woocommerce'),
		    'priority'  => 50,
		    'callback'  => array($this , 'ets_ask_qustion_tab')
    	);  
    	return $tabs;
	}  
 
	/**
	* Save The post Question.
	*/
	public function ets_post_qusetion_answer(){ 
		$productId = $_POST['product_id']; 
		$current_url = get_permalink( $productId );  
		$current_user = wp_get_current_user(); 
		$userProfileUrl = get_author_posts_url($current_user);
		$userEmail = $current_user->user_email;
		$admin_email = get_option('admin_email'); 
		$question = $_POST['question'];  
		$etsCustomerId = $_POST['customer_id'];
		$etsCustomerEmail = $_POST['usermail'];
		$etsCustomerName = $_POST['customer_name']; 
		$productTitle = $_POST['ets_Product_Title']; 
 		$date = date("d-M-Y"); 
 		if(!empty($question)){ 
			$etsUserQusetion =  array(	
				'question' 			=> $question,
				'answer'			=> '',
				'user_name' 		=> $etsCustomerName,
				'user_email' 		=> $etsCustomerEmail,
				'product_title' 	=> $productTitle,
				'user_id' 			=> $etsCustomerId,
				'date'				=> $date,
			);  

			$etsBlankArray = array();
			$etsGetQuestion = get_post_meta( $productId, 'ets_question_answer', true );
	 	 
			if(!empty($etsGetQuestion)){
				array_push( $etsGetQuestion, $etsUserQusetion); 
				$result = update_post_meta($productId, 'ets_question_answer', $etsGetQuestion);
			} else{
				array_push( $etsBlankArray, $etsUserQusetion ); 
				$result = update_post_meta( $productId, 'ets_question_answer', $etsBlankArray);
			} 
		}	
		
		if($result == true){     
			//send email notification to admin 
			$response = array(
				'productId' 	=> $productId,
				'message' 		=> "Question submit successfully",
				'ets_get_question_data'	=> $result 
			);   
			echo json_encode($response);
			  
			
		} else {
			
			$response = array(	
				'status' => 0, 
				"message"	=> "Question not submit.",  
			); 
			echo json_encode($response);
			
		}
		if($result == true) {
			try{  
				$message = "<a href='$userProfileUrl'>" . $etsCustomerName . "</a> added a question on the <a href='$current_url'> " . $productTitle."</a>:  <br><div style='background-color: #FFF8DC;border-left: 2px solid #ffeb8e;padding: 10px;margin-top:10px;'>". $question."</div>";  
				$to = $admin_email;
		        $subject = "New Question: " . get_bloginfo('name');wp_mail($to, $subject, $message);
			}
			catch(Exception $e)
			{

			} 

		}
		die();
	}  

	/**
	* Question Mail Html
	*/
	public function ets_set_html_content_type() {
		return "text/html";
	}
	
	/**
 	*Create Text Area and Ask button
 	*/
 	public function ets_ask_qustion_tab() {  
 		global $product; 
		$productId = $product->get_id();  
		$productTitle = get_the_title($productId); 
		$user = wp_get_current_user();
		$productQaLength = get_option('ets_product_q_qa_list_length');   
		$current_user = $user->exists();  
		if( $current_user == true ){  
			$uesrName = $user->user_login;
			$userId = $user->ID; 
			$uesrEmail = $user->user_email;


		 	?>
			<form action="#" method="post"  id="ets-qus-form" name="form">  
				<textarea id="myInput" cols="45" rows="3" id="name" class="ets-qa-textarea"   name="question" value="" placeholder="Enter Question Here." height= "75px" ></textarea>
				<input type="hidden" id="useremail" class="productId" name="usermail" value="<?php echo $uesrEmail ?>">
				<input type="hidden" id="custId" class="productId" name="product_id" value="<?php echo $productId ?>">
				<input type="hidden" id="productlength" class="productlength" name="Product_Qa_Length" value="<?php echo $productQaLength ?>">
				<input type="hidden" id="custId" name="customer_id" value="<?php echo $userId ?>">
				<input type="hidden" id="custId" name="customer_name" value="<?php echo $uesrName ?>"> 
				<input type="hidden" id="producttitle" name="ets_Product_Title" value="<?php echo $productTitle ?>">
				<div class="ets-display-message"><p></p></div>
				<div class="ets-dis-message-error"><p></p></div>
				<button id="ets-submit" type="submit" name="submit" class="btn btn-info" >Submit</button> 
			</form> 
			<div id="ets_product_qa_length"><p></p></div>   
			<?php 	
		} else { ?>

				<form action="#" method="post"  id="ets-qus-form" name="form">   
				<input type="hidden" id="custId" class="productId" name="product_id" value="<?php echo $productId ?>">
				<input type="hidden" id="productlength" class="productlength" name="Product_Qa_Length" value="<?php echo $productQaLength ?>">
				<input type="hidden" id="custId" name="customer_id" value="<?php echo$userId ?>">
				<input type="hidden" id="custId" name="customer_name" value="<?php echo $uesrName ?>"> 
				<input type="hidden" id="producttitle" name="ets_Product_Title" value="<?php echo $productTitle ?>"> 
			</form>
			<div id="ets_product_qa_length"><p></p></div> 
				<a href="<?php echo 'http://localhost/wordpress/wp-login.php' ?>" class="ets-load-more">
				Plese Login Your Account.
				</a> 
			<?php  
			}  
			$loadMoreButtonName = get_option('ets_load_more_button_name');
			$productQaLength = get_option('ets_product_q_qa_list_length'); 
			$loadMoreButton = get_option('ets_load_more_button'); 	
			$pagingType = get_option('ets_product_qa_paging_type' ); 
			$etsGetQuestion = get_post_meta( $productId,'ets_question_answer', true );
			if(!empty($etsGetQuestion)){ 
				end( $etsGetQuestion);
				$keyData =  max(array_keys($etsGetQuestion));
            } 
			if($loadMoreButton == "true") { 
				if(empty($loadMoreButtonName)){
					$loadMoreButtonName = "Load More";
					update_option( 'ets_load_more_button_name', $loadMoreButtonName );
				}  
				if(!empty($etsGetQuestion)){ 
					
					$count = 1;
					if (empty($productQaLength)) {    
						$productQaLength = 4;
					}

					//Show Question Answer Listing Accordion Type With Load More Button
					if($pagingType == 'accordion'){
						?>
						<div class='ets-qa-listing'>
						<?php
						foreach ($etsGetQuestion as $key => $value) {
							?>
							<div class="ets-accordion">
								<span class="que-content"><b>Question:</b></span>
								<span class="que-content-des"><?php echo $value['question'];?></span>
								<h6><?php echo $value['user_name']. "<br>";?> <?php echo $value['date'];?></h6>
							</div>
							<div class="ets-panel">
								<?php 
								if(!empty($value['answer'])){?>
									<span class="ans-content"><b>Answer:</b>
									</span>
									<span class="ans-content-des"><?php echo $value['answer'];?>
									</span>
								 
							<?php 
								} else { ?>
								<span class="ans-content"><b>Answer:</b></span>
								<span class="ans-content-des"><?php echo "Answer awaiting...";?>
								</span>
								<?php
							}?>
							</div><?php  
							$count++;
							if($count > $productQaLength){
								break;
							}  
						}
						?> 
						</div>
						<?php
					} else {
						//Show Question Answer Listing Type Table With Load More
						?>
						<div class="table-responsive my-table">
						<table class="table table-striped">
						<?php  
						foreach ($etsGetQuestion as $key => $value) {
							?>
							<tr class="ets-question-top">
								<td class="ets-question-title"><p>Question:</p></td>
								<td class="ets-question-description"><p><?php echo $value['question'];?></p></td> 
								<td class="pull-right"><h6 class="user-name"><?php echo $value['user_name'] . "<br>";    
								echo ($value['date']);
								$value['date'];?></h6></td>
							</tr>
							<?php 
							if(!empty($value['answer'])){?>
								<tr>
									<td class="ets-question-title"><p>Answer:</p></td>
									<td colspan="2"><p> <?php echo $value['answer'];?></p></td> 
								</tr> 
								<?php 
							} else { ?>
								<tr>
									<td class="ets-question-title"><p>Answer:</p></td>
									<td colspan="2"><h6><p> <?php echo "Answer awaiting...";?> </p></h6></td>	
								</tr> 
								<?php
							}
							$count++;
							if($count > $productQaLength){
								break;
							}  
						} ?>
						</table>  
					</div>
					<?php
					}
					 ?> 
					<div class="table1" id="ets-question-detail-ajax"></div>
					<button type="submit" id="ets-load-more" class="btn btn-success" name="ets_load_more" value=""><?php echo $loadMoreButtonName; ?></button>
					<div class="ets_pro_qa_length"><p hidden><?php echo $keyData;?><p></div>
					<?php
				}
			} else {
				//Show Question Answer Listing Type Table With OUt Load More
				if(!empty($etsGetQuestion)){ 
					?>
					<div class="table-responsive my-table">
					<table class="table table-striped"> 
					<?php
					foreach ($etsGetQuestion as $key=>$value) {
						?> 
						<tr class="ets-question-top">
								<td class="ets-question-title"><p>Question:</p></td>
								<td class="ets-question-description"><p><?php echo $value['question'];?></p></td> 
								<td class="pull-right"><h6 class="user-name"><?php echo $value['user_name'] . "<br>";    
								echo ($value['date']);
								$value['date'];?></h6></td>
						</tr>

						<?php 
						if(!empty($value['answer'])){?>
							<tr>
								<td class="ets-question-title"><p>Answer:</p></td>
								<td colspan="2"><p> <?php echo $value['answer'];?></p></td> 
							</tr> 
							<?php 
						} else { ?>
							<tr>
								<td class="ets-question-title"><p>Answer:</p></td>
								<td colspan="2"><h6><p> <?php echo "Answer awaiting...";?> </p></h6></td>	
							</tr> 
							<?php
						}
					}
					?> 
					</table>
					</div>
					<?php
				} 
			}
			?> 
		<div class="ets-question-detail-ajax" id="ets-question-detail-ajax"></div>
  		 
		<?php
	}

	/**
	* Load More Button Post Data Using Ajax
	*/
	public function ets_product_qa_load_more(){ 
		$productId = $_GET['product_id']; 
		$offsetdata = $_GET['offset']; 
		$loadMoreButtonName = get_option('ets_load_more_button_name');
		$pagingType = get_option('ets_product_qa_paging_type' ); 
		$productQaLength = get_option('ets_product_q_qa_list_length');  
		$etsGetQuestion = get_post_meta( $productId,'ets_question_answer', true );
 		$offset = $offsetdata + $productQaLength;  
		array_splice($etsGetQuestion,0,$offset);  

		if(!empty($etsGetQuestion)){ 
			ob_start(); 
			$count = 1;  
			
			//Show Question Answer Listing Accordion Type With Load More Button
			if($pagingType == 'accordion'){
				?>
				<div class='ets-qa-listing'>
				<?php
				foreach ($etsGetQuestion as $key => $value) { 
					?>
					<div class="ets-accordion">
								<span class="que-content ans-content"><b>Question:</b></span>
								<span class="que-content-des"><?php echo $value['question'];?></span>
								<h6><?php echo $value['user_name']. "<br>";?> <?php echo $value['date'];?></h6>
							</div>
							<div class="ets-panel">
								<?php 
								if(!empty($value['answer'])){?>
									<span class="ans-content"><b>Answer:</b>
									</span>
									<span class="ans-content-des"><?php echo $value['answer'];?>
									</span>
								 
							<?php 
								} else { ?>
								<span class="ans-content"><b>Answer:</b></span>
								<span class="ans-content-des"><?php echo "Answer awaiting...";?>
								</span> 
								<?php
							}?>
							</div><?php  
							$count++;
							if($count > $productQaLength){
								break;
							}  
				} 
				?>
				</div>
				<?php 	 
			} else {
				//Show Question Answer Listing Type Table With Load More
				?> 
				<div class="table-responsive my-table">
				<table class="table table-striped"> 
				<?php  

				 foreach ($etsGetQuestion as $key => $value) { 
					?> 
					<tr class="ets-question-top">
						<td class="ets-question-title"><p>Question:</p></td>
						<td class="ets-question-description"><p><?php echo $value['question'];?></p></td> 
						<td class="pull-right"><h6 class="user-name"><?php echo $value['user_name'] . "<br>";    
						echo ($value['date']);
						$value['date'];?></h6></td>
					</tr>
					<?php 
					if(!empty($value['answer'])){?>
						<tr>
							<td class="ets-question-title"><p>Answer:</p></td>
							<td colspan="2"><p> <?php echo $value['answer'];?></p></td> 
						</tr> 
						<?php 
					} else { ?>
						<tr>
							<td class="ets-question-title"><p>Answer:</p></td>
							<td colspan="2"><h6><p> <?php echo "Answer awaiting...";?> </p></h6></td>	
						</tr> 
						<?php
					}
					$count++;
					if($count > $productQaLength){
						break;
					}  
				} 
				?> 
				</table>  
				<?php
				
			}
			$htmlData = ob_get_clean(); 
		}
		$response = array( 
			'htmlData'		=> $htmlData,
			'offset' 		=> $offset, 
		);
		echo json_encode($response);
		die;
	}

	/**
	*  JS Variables
	*/
	public function ets_woo_qa_scripts() {
		wp_enqueue_script( 'ets_woo_qa_script_js', ETS_WOO_QA_PATH . 'asset/js/ets_woo_qa_script.js',array( 'jquery' ),false,true  );

			$script_params = array(
				'admin_ajax' => admin_url('admin-ajax.php')
			);  

	  	wp_localize_script( 'ets_woo_qa_script_js', 'etsWooQaParams', $script_params ); 
	}
	
	public function ets_woo_qa_style() {
		wp_register_style(
		    'ets_woo_qa_style_css',
		    ETS_WOO_QA_PATH. 'asset/css/ets_woo_qa_style.css'
		); 
		wp_enqueue_style( 'ets_woo_qa_style_css');
		 
	}	 
} 			
$etsWooProductUserQuestionAnswer = new ETS_WOO_PRODUCT_USER_QUESTION_ANSWER();	
?>