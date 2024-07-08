import { HttpInterceptor, HttpEvent, HttpResponse, HttpRequest, HttpHandler } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { TokenService } from './src/app/services/token.service';
 
@Injectable()
export class authInterceptor implements HttpInterceptor {
  constructor(private token : TokenService){ }
  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    let token:string = this.token.getToken();
    if(token) {
      const authRequest = req.clone({
        headers: req.headers.set('Authorization', 'Bearer ' + token),
      });
      return next.handle(authRequest);
    } else {
      return next.handle(req);
    }
  }
}