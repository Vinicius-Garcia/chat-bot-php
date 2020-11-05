<?php


class whatsAppBot
{
    
    var $APIurl = "https://eu137.chat-api.com/instance191362/"; // recebe a instancia da api
    var $token = "ze9rukq3bbj867p7";//recebe o token da api

    public function __construct()
    {
        $json = file_get_contents('php://input');
        $decoded = json_decode($json,true);

        ob_start();
        var_dump($decoded);
        $input = ob_get_contents();
        ob_end_clean();
        file_put_contents('input_requests.log',$input.PHP_EOL,FILE_APPEND); //salva o resultado em um arquivo

        if(isset($decoded['messages'])) { //essa validação previne que mensagens do servidor seja reconhecida como mensagens do usuário
            foreach ($decoded['messages'] as $message) {//as mensagens estão em uma matriz
                $text = explode(' ', trim($message['body'])); //dividi a mensagem em palavras separadas e a primeira palavra é o comando
                if (!$message['fromMe']) { //para o bot não pegar mensagem enviada por ele mesmo
                    switch (mb_strtolower($text[0], 'UTF-8')) { //switch para ver qual o comando
                        case 'hi':
                        {
                            $this->welcome($message['chatId'], false);
                            break;
                        }
                        case 'chatId':
                        {
                            $this->showchatId($message['chatId']);
                            break;
                        }
                        case 'time':
                        {
                            $this->time($message['chatId']);
                            break;
                        }
                        case 'me':
                        {
                            $this->me($message['chatId'], $message['senderName']);
                            break;
                        }
                        case 'file':
                        {
                            $this->file($message['chatId'], $text[1]);
                            break;
                        }
                        case 'ptt':
                        {
                            $this->ptt($message['chatId']);
                            break;
                        }
                        case 'geo':
                        {
                            $this->geo($message['chatId']);
                            break;
                        }
                        case 'group':
                        {
                            $this->group($message['author']);
                            break;
                        }
                        default:
                        {
                            $this->welcome($message['chatId'], true);
                            break;
                        }
                    }
                }
            }
        }
    }
    public function sendRequest($method,$data)
    { //realiza a solicitação para o servidor ChatAPI
        $url = $this->APIurl.$method.'?token='.$this->token; //cria uma url valida com a apiurl o método e o token
        if(is_array($data)){ $data = json_encode($data);} //verifica  se os dados estão em array se tiver converte para json se ja tiver em json fica assim
        $options = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/json',
            'content' => $data
        ]]);
        $response = file_get_contents($url,false,$options);//realiza a solicitação para o url
        file_put_contents('requests.log',$response.PHP_EOL,FILE_APPEND);
    }

    public function sendMessage($chatId, $text) //ele forma um array  e envia para a função sendRequest() com o método message
    {
        $data = array('chatId'=>$chatId,'body'=>$text);
        $this->sendRequest('message',$data);
    }
    public function welcome($chatId, $noWelcome = false) //função que lista os comandos disponiveis
    {
        $welcomeString = ($noWelcome) ? "Incorrect command\n" : "WhatsApp Demo Bot PHP\n";
        $this->sendMessage($chatId,
            $welcomeString.
            "Commands:\n".
            "1. chatId - show ID of the current chat\n".
            "2. time - show server time\n".
            "3. me - show your nickname\n".
            "4. file [format] - get a file. Available formats: doc/gif/jpg/png/pdf/mp3/mp4\n".
            "5. ptt - get a voice message\n".
            "6. geo - get a location\n".
            "7. group - create a group with the bot");
    }
    public function showchatId($chatId)//exibe o ID de bate-papo atual usando o comando chatId
    {
        $this->sendMessage($chatId,'chatId: '.$chatId);
    }
    public function time($chatId) // exibe a hora atual do servidor usando o comando time
    {
        $this->sendMessage($chatId,date('d.m.Y H:i:s'));
    }
    public function me($chatId,$name)//exibe o nome do interlocutor usando o comando me
    {
        $this->sendMessage($chatId,$name);
    }
    public function file($chatId,$format)
    {
        $availableFiles = array(
            'doc' => 'document.doc',
            'gif' => 'gifka.gif',
            'jpg' => 'jpgfile.jpg',
            'png' => 'pngfile.png',
            'pdf' => 'presentation.pdf',
            'mp4' => 'video.mp4',
            'mp3' => 'mp3file.mp3'
        );
        if(isset($availableFiles[$format])){
            $data = array(
                'chatId'=>$chatId,
                'body'=>'https://domain.com/PHP/'.$availableFiles[$format],
                'filename'=>$availableFiles[$format],
                'caption'=>'Get your file '.$availableFiles[$format]
            );
            $this->sendRequest('sendFile',$data);}
    }
    public function ptt($chatId) //enviando uma mensagem de voz usando o comando "ptt". A mensagem de voz deve ser um arquivo .OGG no seu servidor
    {
        $data = array(
            'audio'=>'https://domain.com/PHP/ptt.ogg',
            'chatId'=>$chatId
        );
        $this->sendRequest('sendAudio',$data);
    }
    public function geo($chatId) //envio de coordenadas geográficas usando o comando "geo"
    {
        $data = array(
            'lat'=>51.51916,
            'lng'=>-0.139214,
            'address'=>'Your address',
            'chatId'=>$chatId
        );
        $this->sendRequest('sendLocation',$data);
    }



}
new whatsAppBot();