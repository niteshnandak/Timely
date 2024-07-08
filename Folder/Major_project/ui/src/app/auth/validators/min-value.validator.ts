import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

export function minValueValidator(minValue: number): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const value = control.value;
    if (value !== null && value <= minValue) {
      return { minValue: { requiredMinValue: minValue, actualValue: value } };
    }
    return null;
  };
}
