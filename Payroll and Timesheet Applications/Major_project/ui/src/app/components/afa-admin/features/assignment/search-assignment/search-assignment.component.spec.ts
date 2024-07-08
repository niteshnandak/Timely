import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SearchAssignmentComponent } from './search-assignment.component';

describe('SearchAssignmentComponent', () => {
  let component: SearchAssignmentComponent;
  let fixture: ComponentFixture<SearchAssignmentComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SearchAssignmentComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SearchAssignmentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
