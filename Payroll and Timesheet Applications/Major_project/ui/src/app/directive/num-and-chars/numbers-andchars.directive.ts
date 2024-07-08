import { Directive, HostListener } from '@angular/core';

@Directive({
  selector: '[appNumbersAndchars]',
  standalone: true
})
export class NumbersAndcharsDirective {

  private regex: RegExp = new RegExp("^[a-zA-Z0-9-/:]*$");

  constructor() { }

  @HostListener('keypress', ['$event']) OnkeyDown(event: KeyboardEvent) {
    if (!String(event.key).match(this.regex)) {
      event.preventDefault();
    }
  }
}
