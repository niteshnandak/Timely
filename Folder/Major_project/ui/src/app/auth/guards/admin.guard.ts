import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../auth-services/auth.service';
import { inject } from '@angular/core';

export const adminGuard: CanActivateFn = (route, state) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  // if(auth.isLoggedIn() && auth.getRole() === 'admin'){
  //   return true;
  // }

  // CHECK IF ADMIN IS LOGGED IN
  if(auth.isLoggedIn()) {
    if(auth.getRole() === 'admin') {
      return true;
    }
    else if(auth.getRole() === 'user') {
      router.navigate(['dashboard']); // user logged in route
      return false;
    }
  }

  router.navigate(['login']);
  return false;
};
