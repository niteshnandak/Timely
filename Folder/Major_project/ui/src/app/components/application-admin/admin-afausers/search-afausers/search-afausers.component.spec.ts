import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SearchAfausersComponent } from './search-afausers.component';

describe('SearchAfausersComponent', () => {
  let component: SearchAfausersComponent;
  let fixture: ComponentFixture<SearchAfausersComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SearchAfausersComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SearchAfausersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
