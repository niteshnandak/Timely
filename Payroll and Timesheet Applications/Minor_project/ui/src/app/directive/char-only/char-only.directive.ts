import { Directive, HostListener, Input } from '@angular/core';

@Directive({
  selector: '[appCharOnly]',
  standalone: true
})
export class CharOnlyDirective {
@Input() length:number = 10;

  private regex: RegExp = new RegExp("^[a-zA-Z]+$");
  constructor() { }
  @HostListener('keypress', ['$event']) OnkeyDown(event: KeyboardEvent) {
    if (!String(event.key).match(this.regex)) {
      console.log(event.key)
      event.preventDefault();
    }
  }

}
