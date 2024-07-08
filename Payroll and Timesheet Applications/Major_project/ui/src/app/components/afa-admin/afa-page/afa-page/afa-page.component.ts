import { Component } from '@angular/core';
import { SideNavbarComponent } from "../../../side-navbar/side-navbar.component";
import { RouterOutlet } from '@angular/router';
import { LoaderComponent } from '../../../loader/loader.component';

@Component({
    selector: 'app-afa-page',
    standalone: true,
    templateUrl: './afa-page.component.html',
    styleUrl: './afa-page.component.css',
    imports: [SideNavbarComponent,RouterOutlet, LoaderComponent]
})
export class AfaPageComponent {

    // public isLoading !: boolean ;
    handleLoad(event: any){
        console.log(event);
    }
}
