import { Directive, HostListener } from '@angular/core';

@Directive({
  selector: '[appDecimalNumberOnly]',
  standalone: true
})
export class DecimalNumberOnlyDirective {

  private regex: RegExp = new RegExp("^^[0-9]*\\.?[0-9]{0,2}$");

  constructor() { }

  @HostListener('keypress', ['$event']) onKeyPress(event: KeyboardEvent) {
    const key = event.key;

    // Allow control keys (backspace, delete, arrow keys, etc.)
    if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight'].includes(key)) {
      return;
    }

    // Get the current value of the input field
    const current: string = (event.target as HTMLInputElement).value;

    // If the key is not a number and it's not a dot, prevent input
    if (!key.match(/[0-9.]/)) {
      event.preventDefault();
      return;
    }

    // If the key is a dot, check if there is already a dot in the value
    if (key === '.' && current.includes('.')) {
      event.preventDefault();
      return;
    }

    // Create a new value if this key is added
    const newValue = current + key;

    // If the new value does not match the regex, prevent input
    if (!newValue.match(this.regex)) {
      event.preventDefault();
      return;
    }
  }

  @HostListener('paste', ['$event']) onPaste(event: ClipboardEvent) {
    const clipboardData = event.clipboardData;
    const pastedText = clipboardData?.getData('text') || '';

    // Create a new value if this text is pasted
    const current: string = (event.target as HTMLInputElement).value;
    const newValue = current + pastedText;

    // If the new value does not match the regex, prevent paste
    if (!newValue.match(this.regex)) {
      event.preventDefault();
      return;
    }
  }
}
