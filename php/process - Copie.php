<?php

    echo file_get_contents("../wait.html");
    $rep_choices = array(1=>"K2_Matrix", 2=>"K2_List" , 3=>"Graph");
    $rep_choices_dyn =  array(0 => "IK2", 1=>"DIK2");


    $meth = '';

    $execdir = "../x64/Release/";
    //get the file and save it in the data folder
    $targetdir = './../data/';  
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
                
                if(isset($_POST['k'],$_POST['Rep_type'],$_POST['graph_type'])){
					$meth='K2_out_'.$a[0].'.txt';
                    $rep_choice = $rep_choices[$_POST['Rep_type']] ; 
                    $oriented = $_POST['graph_type'];
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
                            $cmd = $cmd . ' -compress ' ;
					 $meth = $a[0] . '.g';
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
                $order_type = $_POST['order_type'] - 1;

                $cmd = $cmd . " Graph " . $k . " " . $order_type;
                break;
            
        }



        //C++ call for compression of the graph 
	exec('cd ../x64/Release/ & '.$cmd . ' & cd ../../php ',$output1,$returnValue1);
    
    echo $cmd;  


        //print message end 
    $taux =0;
    $tmp = 0;
	$nodes_txt = '';
	$edges='';
     
        // $edges ="test";

        $file = fopen($targetfile,"r");

        if ($_POST['Rep_type'] == 1 || $_POST['Rep_type'] ==2 ) $ch = fgets($file) ;

        while(! feof($file))
        {
            $ch = fgets($file) ;
            if($ch[0]!='#'){
                $a = explode(" ", $ch) ;
               
                 $edges = $edges . '{from: '. @$a[0].', to: '. @$a[1].', width: 1},' ;
                 $s1 = preg_replace('/[^0-9\-]/', '', @$a[0]); 
                 $s2 = preg_replace('/[^0-9\-]/', '', @$a[1]); 
              
                 if (strpos($nodes_txt, $s1) == false)
                 {
                     $nodes_txt = $nodes_txt . '{id: '.$s1.', label: "'.$s1.'"},';
                 }
             
                 if (strpos($nodes_txt, $s2) == false)
                 {
             
                     $nodes_txt = $nodes_txt . '{id: '.$s2.', label: "'.$s2.'"},';
                 }
                 
            }
            
        }

       fclose($file);

$downloadFile = $out .''. $meth;

//echo $downloadFile;

    $str = '
        <script>
            var myVar;

                function myFunction() {
                   showPage();
                }

                function showPage() {

                    var nodes = new vis.DataSet([
                       
                        '.$nodes_txt.'
                        
                      ]);
                    
                     
                      var edges = new vis.DataSet([
                        '.$edges.'
                      ]);
                    
            
                      var container = document.getElementById(\'mynetwork\');
                      var data = {
                        nodes: nodes,
                        edges: edges
                      };
                      var options = {};
                      var network = new vis.Network(container, data, options);


                    $("<li>Ratio de compression : '. $output1[1]. ' </li>").appendTo("#result");
                    $("<li>Nombre de bits par noeud : '. $output1[2] . ' bits</li>").appendTo("#result");
                    $("<li>Temps d\'execution : '. $output1[3] . '</li>").appendTo("#result");

                    $("<li><a href=\' ' .$downloadFile .'\' download=\'ouput.txt\'>T&eacute;l&eacute;charger le fichier <br/>du graphe compr&eacute;ss&eacute;</a></li>").appendTo("#output")


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
