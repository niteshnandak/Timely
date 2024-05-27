import { Component,EventEmitter,Input, Output } from '@angular/core';
import { navbarData } from './nav-data';
import { CommonModule, NgClass } from '@angular/common';
import { BrowserModule } from '@angular/platform-browser';
import { Router, RouterLink, RouterOutlet } from '@angular/router';

interface SideNavToggle {
  screenWidth: number;
  collapsed: boolean;
}

@Component({
  selector: 'app-sidenavbar',
  standalone: true,
  imports: [
    NgClass,
    CommonModule,
    RouterLink
  ],
  templateUrl: './sidenavbar.component.html',
  styleUrl: './sidenavbar.component.css'
})


export class SidenavbarComponent {

  @Output() onToggleSidenavbar: EventEmitter<SideNavToggle> = new EventEmitter();
  collapsed = false;
  screenWidth= 0;
  navData = navbarData;

  toggleCollapse(): void {
    this.collapsed = !this.collapsed;
    this.onToggleSidenavbar.emit({collapsed: this.collapsed, screenWidth: this.screenWidth});
  }

  closeSidenavbar(): void {
    this.collapsed = false;
    this.onToggleSidenavbar.emit({collapsed: this.collapsed, screenWidth: this.screenWidth});
  }

}
