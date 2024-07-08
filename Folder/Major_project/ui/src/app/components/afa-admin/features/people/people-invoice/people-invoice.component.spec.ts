import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PeopleInvoiceComponent } from './people-invoice.component';

describe('PeopleInvoiceComponent', () => {
  let component: PeopleInvoiceComponent;
  let fixture: ComponentFixture<PeopleInvoiceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PeopleInvoiceComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PeopleInvoiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
