import { Directive, HostListener } from '@angular/core';

@Directive({
  selector: '[appCharWithSpace]',
  standalone: true
})
export class CharWithSpaceDirective {

  private regex: RegExp = new RegExp("^[a-zA-Z ]*$");
  constructor() { }
  @HostListener('keypress', ['$event']) OnkeyDown(event: KeyboardEvent) {
    if (!String(event.key).match(this.regex)) {
      console.log(event.key)
      event.preventDefault();
    }
  }

}
