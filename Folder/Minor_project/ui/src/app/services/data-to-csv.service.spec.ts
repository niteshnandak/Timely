import { TestBed } from '@angular/core/testing';

import { DataToCsvService } from './data-to-csv.service';

describe('DataToCsvService', () => {
  let service: DataToCsvService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(DataToCsvService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
