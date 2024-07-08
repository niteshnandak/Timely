import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../auth-services/auth.service';

export const userGuard: CanActivateFn = (route, state) => {
  const auth = inject(AuthService);
  const router = inject(Router);

  // if(auth.isLoggedIn() && auth.getRole() === 'user'){
  //   return true;
  // }

  // CHECK IF USER IS LOGGED IN
  if(auth.isLoggedIn()) {
    if(auth.getRole() === 'user') {
      return true;
    }
    else if(auth.getRole() === 'admin') {
      router.navigate(['app-admin/organisation']); // admin logged in route
      return false;
    }
  }

  router.navigate(['login']);
  return false;};
