// src/composables/useAuth.js
import { ref, onMounted } from 'vue';
import { 
  auth,
  db,
  onAuthStateChanged,
  doc,
  setDoc,
  serverTimestamp
} from '@/firebase';

export const useAuth = () => {
  const user = ref(null);
  const error = ref(null);
  const loading = ref(true); // Inicia en true para indicar carga inicial

  // Escuchar cambios de autenticación
  onMounted(() => {
    onAuthStateChanged(auth, (firebaseUser) => {
      user.value = firebaseUser;
      loading.value = false;
      
      if (firebaseUser) {
        localStorage.setItem('user', JSON.stringify({
          uid: firebaseUser.uid,
          email: firebaseUser.email,
          displayName: firebaseUser.displayName
        }));
        localStorage.setItem('isLoggedIn', 'true');
      } else {
        localStorage.removeItem('user');
        localStorage.removeItem('isLoggedIn');
      }
    });
  });

  return {
    user,
    error,
    loading
  };
};