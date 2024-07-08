import { Directive } from '@angular/core';

@Directive({
  selector: '[appMaxLength]',
  standalone: true
})
export class MaxLengthDirective {

  constructor() { }

}
