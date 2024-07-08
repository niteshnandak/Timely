import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'ismatch',
  standalone: true,
})
export class IsmatchPipe implements PipeTransform {
  transform(value1: any, value2: any) {
    return value1 == value2 ? true : false;
  }
}
