import { Component,Input, Output } from '@angular/core';
import { CommonModule, NgClass } from '@angular/common';
import { Router, RouterLink, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-body',
  standalone: true,
  imports: [
    NgClass,
    CommonModule,
    RouterLink,
    RouterOutlet
  ],
  templateUrl: './body.component.html',
  styleUrl: './body.component.css'
})
export class BodyComponent {

  @Input() collapsed = false;
  @Input() screenWidth = 0;
  getBodyClass(): string {
    let styleClass ='';
    if (this.collapsed && this.screenWidth > 768) {
      styleClass = 'body-trimmed';
    } else if (this.collapsed && this.screenWidth > 768 && this.screenWidth > 0){
      styleClass = 'body-md-screen'
    }
    return styleClass;

  }
}
