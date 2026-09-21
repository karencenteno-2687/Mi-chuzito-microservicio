import { initializeApp } from "firebase/app"
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber } from "firebase/auth"

const firebaseConfig = {
    apiKey:            "AIzaSyDGI3Zjv1RDmb6YTi97VS8yx_UJ2WHfZbk",
    authDomain:        "mi-chuzito.firebaseapp.com",
    projectId:         "mi-chuzito",
    storageBucket:     "mi-chuzito.firebasestorage.app",
    messagingSenderId: "23262445421",
    appId:             "1:23262445421:web:bf86b6f77d18e07607a464"
}

const app  = initializeApp(firebaseConfig)
const auth = getAuth(app)

export { auth, RecaptchaVerifier, signInWithPhoneNumber }