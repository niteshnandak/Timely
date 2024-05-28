import { Component,EventEmitter,HostListener,Input, OnInit, Output } from '@angular/core';
import { navbarData } from './nav-data';
import { CommonModule, NgClass } from '@angular/common';
import { Router, RouterLink, RouterOutlet, RouterLinkActive } from '@angular/router';
import { trigger, state, style, transition, animate,keyframes } from '@angular/animations';

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
    RouterLink,
    RouterLinkActive,
    
  ],
  templateUrl: './sidenavbar.component.html',
  styleUrl: './sidenavbar.component.css',
  animations: [
  //   trigger('fadeIn', [
  //     transition(':enter',[
  //       style ({opacity: 0}),
  //       animate('350ms',
  //         style ({opacity: 1})       
  //        )
  //     ]),
  //     transition(':leave',[
  //       style ({opacity: 1}),
  //       animate('350ms',
  //         style ({opacity: 0})       
  //        )
  //     ])
  // ]),
  trigger('rotate', [
    transition(':enter', [
      animate('1000ms',
      keyframes([
        style({transform: 'rotate(0deg)', offset: '0'}),
        style({transform: 'rotate(2turn)', offset: '1'})
      ])
     )
    ])
  ])
  ]
})


export class SidenavbarComponent implements OnInit {

  @HostListener('window:resize')
  onResize() {
    this.screenWidth = window.innerWidth;
    if(this.screenWidth <= 768){
      this.collapsed = false;
      this.onToggleSidenavbar.emit({collapsed: this.collapsed, screenWidth: this.screenWidth});

    }
  }

  constructor() { }

  ngOnInit(): void {
    this.screenWidth = window.innerWidth;
  }

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
