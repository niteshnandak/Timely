import { AbstractControl, ValidatorFn } from '@angular/forms';

export function maxYearValidator(): ValidatorFn {
  return (control: AbstractControl): { [key: string]: boolean } | null => {
    if (!control.value) {
      return null;
    }
    const currentYear = new Date().getFullYear();
    const selectedYear = new Date(control.value).getFullYear();

    if (selectedYear > currentYear) {
      return { maxYear: true };
    }

    return null;
  };
}
