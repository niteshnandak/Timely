import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { SidenavbarComponent } from "./sidenavbar/sidenavbar.component";
import { BodyComponent } from './body/body.component';

interface SideNavToggle {
  screenWidth: number;
  collapsed: boolean;
}

@Component({
    selector: 'app-root',
    standalone: true,
    templateUrl: './app.component.html',
    styleUrl: './app.component.css',
    imports: [RouterOutlet, SidenavbarComponent, BodyComponent]
})
export class AppComponent {
  title = 'final_project_p1_skeletal';

  isSideNavCollapsed = false;
  screenWidth = 0;

  onToggleSideNav(data: SideNavToggle) : void {
      this.screenWidth = data.screenWidth;
      this.isSideNavCollapsed = data.collapsed;
  }
}
