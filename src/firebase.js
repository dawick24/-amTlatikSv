// src/firebase.js (MANTIENE tu código actual, solo AGREGA)
import { initializeApp } from 'firebase/app';
import { getFirestore, doc, setDoc, serverTimestamp } from 'firebase/firestore'; // <- AGREGAR
import { 
  getAuth, 
  createUserWithEmailAndPassword,  // <- AGREGAR
  signInWithEmailAndPassword,      // <- AGREGAR  
  signOut,                         // <- AGREGAR
  updateProfile,                   // <- AGREGAR
  onAuthStateChanged               // <- AGREGAR
} from 'firebase/auth';

// TU CONFIGURACIÓN DE FIREBASE (MANTIENE IGUAL)
const firebaseConfig = {
  apiKey: "AIzaSyAwhRulHXDjpqnRQBD8wTRlWl0WRf2hrWs",
  authDomain: "proyecto-21-93b2c.firebaseapp.com",
  projectId: "proyecto-21-93b2c",
  storageBucket: "proyecto-21-93b2c.firebasestorage.app",
  messagingSenderId: "352514079037",
  appId: "1:352514079037:web:dbe38c4cb6f193e4036d54"
};

// Initialize Firebase (MANTIENE IGUAL)
const app = initializeApp(firebaseConfig);

// Initialize Firestore (MANTIENE IGUAL)
const db = getFirestore(app);

// Initialize Authentication (MANTIENE IGUAL)
const auth = getAuth(app);

// EXPORTAR (AGREGA las nuevas funciones)
export { 
  db, 
  auth,
  // NUEVAS EXPORTACIONES PARA LOGIN:
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  signOut,
  updateProfile,
  onAuthStateChanged,
  doc,
  setDoc,
  serverTimestamp
};
export default app;