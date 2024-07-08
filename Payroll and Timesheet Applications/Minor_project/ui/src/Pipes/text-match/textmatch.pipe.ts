import { Pipe, PipeTransform } from '@angular/core';


@Pipe({
  name: 'textmatch',
  standalone: true
})
export class TextmatchPipe implements PipeTransform {

  transform(value1: any, value2: any) {

    return value1 !== value2 ? false : true;
  }

}
