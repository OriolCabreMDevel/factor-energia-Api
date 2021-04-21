<?php

    namespace App\Controller;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Symfony\Component\HttpClient\HttpClient;

    /**
     * @Route("/api", name="api")
     */

    class QuestionController extends AbstractController{
        /*
         * Utilities
         * 
         */



        /*
        * this is a function that generates an stack exchange connection URL 
        * then if it's not a boolean return an array
        *  nice! fresh data to mess with
        */
         private function genUrl($params){
            
            $url="https://api.stackexchange.com/2.2/questions?".$params;
            //var_dump($url);

            $client = HttpClient::create();
            $response = $client->request('GET',$url);
     
            if($response !== false ){
         
                return $response->toArray(); 
            
            }else{

                return $response =["err"=>"opsi: something went wrong with the given parameters check it please"];
            }
         }

        /* a simple way to check dates not great really */
        private function validateDate($date){
     
             $splitedDate=preg_split("/[\s,-]+/",$date);
             $countdate =count($splitedDate);   

             if($countdate===3){
                //i could use some sorting system but kiss rule 
                $response = checkdate($splitedDate[1],$splitedDate[2],$splitedDate[0]);   
                if($response==false){
                    $response = checkdate($splitedDate[0],$splitedDate[2],$splitedDate[1]);   
                }
             }else{
     
                 $response=false;
     
             }
                
                
             return $response;
             
         
         }

        /* Getters */

        /**
         * get all questions in order dec or asc slug == order value
         * 
         * @Route("/order/", name="_order", methods={"GET"})
         
         */
        function getQuestionsInOrder(Request $req){

         // get data from the Request object from HttpFoundation
        
           $slug=json_decode($req->query->all()['slug']);  

         // i know that there is a better way 
         //i don't remember it now and this just works
           $response = $this->genUrl("order=".$slug."&site=stackoverflow");
         
            return $this->json($response);
        }
        
        
        /**
         * get all questions relative to a tag in  slug == array[ order, tag]
         * 
         * @Route("/tagged", name="_tagged", methods={"GET"})
         
         */
        function getTaggeedQuestionsInOrder(Request $req){
          
    
        $slug=json_decode($req->query->all()['slug']);

        $response = $this->genUrl("order=".$slug[0]."&tagged=".$slug[1]."&site=stackoverflow");

        return $this->json($response);

        }


         /**
         * get all questions relative to a tag in the slug == array[ order, tag] 
         * 
         * @Route("/tagged-latest-activity/", name="_tagged_latest_activity", methods={"GET"})
         */
         function getTaggeedQuestionsInOrderLatestActivity(Request $req){
            
            $slug=json_decode($req->query->all()['slug']);
            $dateNow= date('y-m-d');
           

            $response = $this->genUrl("order=".$slug[0]."&tagged=".$slug[1]."&activity=".$dateNow."&site=stackoverflow");
            return $this->json($response);
        }


        /**
        * get latest questions in order slug == ["asc"||"desc", date yyyy-mm-dd] 
        * 
        * @Route("/latest-date/", name="_latest_date", methods={"GET"})
         
         */
        function getFromDateQuestionsInOrder(Request $req){
     
            $slug=json_decode($req->query->all()['slug']);    

            $date = date($slug[1]);

            if($this->validateDate($date)==true){

                $response = $this->genUrl("order=".$slug[0]."&fromdate=".$date."&site=stackoverflow");

            }else{

                $response =["err"=>"slug[1] is a formdate is a date kep the format yyyy-mm-dd"]; 
                
            }
            return $this->json($response);
        }
        

        /**
        * get all questions from date to date in order slug == ["asc"||"desc", date yyyy-mm-dd, date yyyy-mm-dd] 
        *
        * 
        * @Route("/from-date-to-date/", name="_from_date_to_date", methods={"GET"})
        */
        function getFromDateToDateQuestionsInOrder(Request $req){

            $slug=json_decode($req->query->all()['slug']);

            $fromdate=date($slug[1]);

            if($this->validateDate($fromdate)==true){
            
                $todate=date($slug[2]);
            
               if($this->validateDate($todate)==true){
            
                $response = $this->genUrl("order=".$slug[0]."&fromdate=".$fromdate."&todate=".$todate."&site=stackoverflow");
                
            }else{
                $response =["err"=>"slug[2] is a todate is a date kep the format yyyy-mm-dd"];
            
            }
            }else{
            
                $response =["err"=>"slug[1] is a formdate is a date kep the format yyyy-mm-dd"]; 
                
            }
            return $this->json($response);
        }

        
        /**
        * get all tag questions sorted by votes from max to min in order slug== ["asc"||"desc", tag, max(int),min(int)]
        *
        * 
         * @Route("/tag-sorted-by-votes/", name="_tag_sorted_by_votes", methods={"GET"})
         */
        function getTagSortedByVotes(Request $req){

            $slug=json_decode($req->query->all()['slug']);
          
            if(is_numeric($slug[2])==true){
                
                if(is_numeric($slug[3])==true){

                    $response = $this->genUrl("order=".$slug[0]."&tagged=".$slug[1]."&max=".$slug[2]."&min=".$slug[3]."&sort=votes&site=stackoverflow");
                
                }else{
                    $response = ["err"=>"slug[3] is min an integer"];
                }
            }else{
                $response = ["err"=>"slug[2] is max an integer"];
            }
            return $this->json($response);
        }

    }