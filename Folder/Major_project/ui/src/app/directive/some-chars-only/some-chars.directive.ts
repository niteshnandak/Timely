import { Directive, HostListener } from '@angular/core';

@Directive({
  selector: '[appSomeChars]',
  standalone: true
})
export class SomeCharsDirective {

  constructor() { }

  private regex: RegExp = new RegExp("^[a-zA-Z0-9-\/., ]*$");

  @HostListener('keypress', ['$event']) OnkeyDown(event: KeyboardEvent) {
    if (!String(event.key).match(this.regex)) {
      event.preventDefault();
    }
  }

}
