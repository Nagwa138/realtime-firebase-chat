importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "AIzaSyB339Y-cn5ytAXawM6a-HnomPkULC8_tek",
    authDomain: "chat-2fe32.firebaseapp.com",
    projectId: "chat-2fe32",
    storageBucket: "chat-2fe32.appspot.com",
    messagingSenderId: "456908699383",
    appId: "1:456908699383:web:e47265464a6851d74a05f2",
    measurementId: "G-X60E9BF98T"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function({data:{title,body,icon}}) {
    return self.registration.showNotification(title,{body,icon});
});
