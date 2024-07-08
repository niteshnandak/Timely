import { AbstractControl, ValidatorFn } from '@angular/forms';

export function decimal10_2Validator(): ValidatorFn {
  return (control: AbstractControl): {[key: string]: any} | null => {
    const value = control.value;

    if (value === null || value === '') {
      return null;  // consider empty inputs as valid
    }

    // Regex for decimal(10,2) format
    const regex = /^\d{1,8}(\.\d{0,2})?$/;

    if (!regex.test(value)) {
      return { 'decimal10_2': true };
    }

    const parts = value.toString().split('.');
    const integerPart = parts[0];
    const decimalPart = parts[1] || '';

    if (integerPart.length > 8) {
      return { 'decimal10_2': true, 'reason': 'integer part too long' };
    }

    if (decimalPart.length > 2) {
      return { 'decimal10_2': true, 'reason': 'decimal part too long' };
    }

    // Check if the total number of digits doesn't exceed 10
    if (integerPart.length + decimalPart.length > 10) {
      return { 'decimal10_2': true, 'reason': 'total digits exceed 10' };
    }

    return null;
  };
}
