import { Directive, HostListener } from '@angular/core';

@Directive({
  selector: '[appDecimalOnly]',
  standalone: true
})
export class DecimalOnlyDirective {

  private regex: RegExp = new RegExp("^(.[0-9])?$");

  constructor() { }

  @HostListener('keypress', ['$event']) OnkeyDown(event: KeyboardEvent) {
    console.log(event.key)
    if (!String(event.key).match(this.regex)) {
      event.preventDefault();
    }
  }
}
