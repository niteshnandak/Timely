import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

export function emailOrUsernameValidator(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const value = control.value;

    // Basic checks
    if (!value) {
      return { required: true };
    }

    // Regular expressions for username and email
    const usernameRegex = /^[a-zA-Z0-9._]{3,}$/;
    const emailRegex = /^[\w\.-]+@[\w\.-]+\.\w{2,4}$/;

    const isValidUsername = usernameRegex.test(value);
    const isValidEmail = emailRegex.test(value);

    // Validate the input
    if (!isValidUsername && !isValidEmail) {
      return { emailOrUsername: true };
    }

    return null; // Validation successful
  };
}
