@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('Toast.min.css')}}">

    <style>
        .messages-container {
            height: 60vh;
            overflow-y: auto;
            flex-direction: column;
        }

        .message {
            background: yellow;border: 1px solid;border-bottom-left-radius: 20px;padding: 20px;width: fit-content;
        }
    </style>
    <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">{{ __('Friends') }}</div>
                <div class="card-body">
                    @foreach(\App\Models\User::all() as $user)
                        @if($user->id != auth()->id())
                            <div class="d-flex align-items-center align-content-center">

                                <img class="rounded-5" style="width: 50px; margin-right: 20px" src="https://yt3.googleusercontent.com/-CFTJHU7fEWb7BYEb6Jh9gm1EpetvVGQqtof0Rbh-VQRIznYYKJxCaqv_9HeBcmJmIsp2vOO9JU=s900-c-k-c0x00ffffff-no-rj" >
                                <div>
                                    <h5>
                                        {{$user->name}}
                                    </h5>
                                    <p>
                                        {{$user->email}}
                                    </p>
                                </div>
                            </div>
                            <hr>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Conversation') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class=" messages-container">
                        @forelse(\App\Models\Message::orderBy('id', 'asc')->get() as $message)

                            <p class="message">
                                <span style="color:dodgerblue"> {{ '@' . $message->receiver->name }}</span>
                                <br>
                                {{$message->text}}
                                <br>
                                {{ 'From: ' . $message->sender->name }}
                            </p>
                        @empty
                            No messages needed
                        @endforelse

                    </div>

                                    <div class="container">
                                        <div class="row">
                                            <select class="form-control col" name="receiver_id">
                                                @foreach(\App\Models\User::all() as $user)
                                                    <option value="{{$user->id}}">
                                                        {{$user->name}}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input class="form-control col" type="text" name="text" >
                                            <button class="btn btn-primary col" onclick="sendMessage()">
                                                Send
                                            </button>
                                        </div>
                                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<!-- The core Firebase JS SDK is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>

<!-- TODO: Add SDKs for Firebase products that you want to use
    https://firebase.google.com/docs/web/setup#available-libraries -->

    <script type="text/javascript" src="{{asset('Toast.min.js')}}"></script>
<script>

    var messagesContainer = document.querySelector('.messages-container');

    messagesContainer.scrollTo(0,messagesContainer.scrollHeight)

    // Your web app's Firebase configuration

    var firebaseConfig = {
        apiKey: "AIzaSyB339Y-cn5ytAXawM6a-HnomPkULC8_tek",
        authDomain: "chat-2fe32.firebaseapp.com",
        projectId: "chat-2fe32",
        storageBucket: "chat-2fe32.appspot.com",
        messagingSenderId: "456908699383",
        appId: "1:456908699383:web:e47265464a6851d74a05f2",
        measurementId: "G-X60E9BF98T"
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();

    function initFirebaseMessagingRegistration() {
        messaging.requestPermission().then(function () {
            return messaging.getToken()
        }).then(function(token) {

            axios.post("{{ route('fcmToken') }}",{
                _method:"PATCH",
                token
            }).then(({data})=>{
                console.log(data)
            }).catch(({response:{data}})=>{
                console.error(data)
            })

        }).catch(function (err) {
            console.log(`Token Error :: ${err}`);
        });
    }

    initFirebaseMessagingRegistration();

    messaging.onMessage(function({data:{title,body}}){

        body = JSON.parse(body);
        new Toast({
            message: title,
            type: 'success',
        });

        var newMessage = `
                <span style="color:dodgerblue">@${body.receiver.name}</span>
                <br>
                    ${body.message.text}
                    <br>
                    From: ${body.sender.name}
            `;

        var newMessageElement = document.createElement('p');

        newMessageElement.classList.add('message');

        newMessageElement.innerHTML = newMessage;

        messagesContainer.appendChild(newMessageElement)

        messagesContainer.scrollTo(0,messagesContainer.scrollHeight)

    });


    function sendMessage() {

        var receiver = document.querySelector('select[name="receiver_id"]');
        var text = document.querySelector('input[name="text"]');

        var data = {
            _token: '{{csrf_token()}}',
            receiver_id: receiver.value,
            text: text.value,
        }

        axios.post("{{ route('message') }}",{
            _method:"POST",
            data
        }).then(({data})=>{
            document.querySelector('input[name=text]').value = '';

            var newMessage = `
                <span style="color:dodgerblue">@${data.receiver.name}</span>
                <br>
                    ${data.message.text}
                    <br>
                    From: ${data.sender.name}
            `;

            var newMessageElement = document.createElement('p');

            newMessageElement.classList.add('message');

            newMessageElement.innerHTML = newMessage;

            messagesContainer.appendChild(newMessageElement)

            messagesContainer.scrollTo(0,messagesContainer.scrollHeight)

        }).catch((error)=> {
            console.error(error)
        })
    }
</script>
@endsection
