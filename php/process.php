<?php

    echo file_get_contents("../wait.html");
    $rep_choices = array(1=>"K2_Matrix", 2=>"K2_List" , 3=>"Graph");
    $rep_choices_dyn =  array(0 => "IK2", 1=>"DIK2");


    $meth = '';
    $bol=false;

    $execdir = "../x64/Release/";
    //get the file and save it in the data folder
    $targetdir = './../BVTest/data/';  
    $targetfile = $targetdir. basename($_FILES['graph']['name']);

    $filename = $_FILES['graph']['name'];

    $out = './../BVTest/data/';
   

    if ( $filename !='' && move_uploaded_file($_FILES['graph']['tmp_name'], $targetfile)) {
    // file uploaded succeeded
         $a = explode(".", $filename) ;
       
         $cmd =  "BVTest.exe " . $_POST['motor'] . " " . $a[0];
         
         switch ($_POST['motor'] ) {
            case 3:
                //k2-GraCE
                $k = $_POST['k'];
                
                if(isset($_POST['k'],$_POST['Rep_type'])){
                  $bol =true;
					$meth='K2_out_'.$a[0].'.txt';
                    $rep_choice = $rep_choices[$_POST['Rep_type']] ; 
                    $oriented =  isset($_POST['graph_type']) ? 1:0;
                    $cmd = $cmd . " " . $rep_choice . " " . $k . " " . $oriented;
                }
                else //dynamic ik2
                {
				    $meth = "IK2_out_".$a[0].'.txt';;
                    if(isset($_POST['k'], $_POST['diff'])) $cmd = $cmd . " DIK2 " . $k . " 0";
                    elseif(isset($_POST['k'])) $cmd = $cmd . " IK2 " . $k . " 0";
                    else echo "error";
                }
                    

                break;
            case 4:
                //P-GracE
                $method = $_POST['mthd'];
                if($method!=''){
                    $cmd = $cmd . ' ' . $method . ' ';
                    switch($method){
                        case "GCUPM":
					 $meth = 'GCUPM_out_'.$a[0].'.txt';
                            $size = $_POST['Pattern_size'];
                            $type = $_POST['Pattern_type'] - 1;
                            $cmd = $cmd . ' ' . $size . ' ' . $type;
                        break;

                        case "VoG":
                        break;

                        case "Subdue":
                            $nSubs = $_POST['nSubs'];
                            $cmd = $cmd . ' -compress ' . '../../BVTest/data/' . $filename ;
					 		$meth = $filename.'.cmp';
                        break;

                        case "DSM":
					$meth = 'DSM_out_'.$a[0].'.txt';
                            $nb_pass = $_POST['nb_iter'];
                            $nb_hash = $_POST['nb_hash'];
                            $cmd = $cmd . ' ' . $nb_hash . ' ' . $nb_pass ;
                        break;

                    }

                }else {
                    echo 'error';
                }
                break;
            case 5:
                 //k2-GraCE with node ordering
                $k = $_POST['k'] ;
                $order_type = $_POST['order_type']  ;

                $cmd = $cmd . " Graph " . $k . " " . $order_type;
                break;
            
        }



        //C++ call for compression of the graph 
	exec('cd ../x64/Release/ & '.$cmd . ' & cd ../../php ',$output1,$returnValue1);
    
   /// echo $cmd;  
	//echo $cmd;

        //print message end 
    $taux =0;
    $tmp = 0;
	$nodes_txt = '';
	$edges='';
     
       

$downloadFile = $out .''. $meth;

//echo $downloadFile;

    $str = '
        <script>
            var myVar;

                function myFunction() {
                   showPage();
                }

                function showPage() {
                   
                	';

    if ( $method !='Subdue') $str=$str.'
                    $("<h1 style=\'font-size:100px\'> '. $output1[1] . ' </h1>").appendTo("#result1");
                    $("<h1 style=\'font-size:100px\'> '. $output1[2] . ' </h1>").appendTo("#result2");
                     $("<h1 style=\'font-size:100px\'> '. $output1[3] . ' </h1>").appendTo("#result3");';
      $str = $str .  '
                    $("<a href=\' ' .$downloadFile .'\' download=\'ouput.txt\' class=\' theme_btn\' style=\'width:100%\'>T&eacute;l&eacute;charger le fichier du graphe compr&eacute;ss&eacute;</a>").appendTo("#output")


                    document.getElementById("loader").style.display = "none";
                    document.getElementById("myDiv2").style.display = "none";
                     document.getElementById("myDiv").style.display = "block";
                }
            </script>
        ' ;

       echo $str ;

    } else { 
       
        echo "not unploaded";
    }



  

?>
