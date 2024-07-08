import { ApplicationConfig } from '@angular/core';
import { provideRouter } from '@angular/router';
import { HttpClient, provideHttpClient, HttpClientModule, HttpClientJsonpModule, HTTP_INTERCEPTORS, withInterceptors, withInterceptorsFromDi } from '@angular/common/http';
import { provideAnimations } from '@angular/platform-browser/animations';


import { routes } from './app.routes';
import { provideToastr } from 'ngx-toastr';
import { authInterceptor } from '../../auth.interceptor';


export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(routes),
    provideHttpClient(withInterceptorsFromDi()),
                {
                  provide: HTTP_INTERCEPTORS,
                  useClass: authInterceptor,
                  multi:true
                },
    provideAnimations(),
    provideToastr({
      preventDuplicates: true,
    })
    ]
}

