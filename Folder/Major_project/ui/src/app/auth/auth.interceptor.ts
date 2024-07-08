import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from './auth-services/auth.service';
import { catchError, throwError } from 'rxjs';
import { ToastrService } from 'ngx-toastr';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const auth = inject(AuthService);
  const toastr = inject(ToastrService);
  const token = localStorage.getItem('token');

  let authReq = req

  // TO SET TOKEN
  if(token){
    authReq = req.clone({
      headers: req.headers.set('Authorization', `Bearer ${token}`)
      // setHeaders: {
      //   Authorization: `Bearer ${token}`
      // }
    });
  }

  // TO CHECK IF TOKEN IS VALID OR NOT
  return next(authReq).pipe(
    catchError((error: HttpErrorResponse) => {
      if(error.status === 401 && error.error.message === 'invalid_token') { //auth:api from backend returns invalid token
        auth.logoutClient();
        toastr.error('Something went wrong');
        router.navigate(['login']);
      }
      return throwError(() => error);
    })
  );
};
