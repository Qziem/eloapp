/* [%bs.raw {|require('./Users.scss')|}]; */
let component = ReasonReact.statelessComponent("Operations");

let make = _children => {
  ...component,
  render: _self =>
    <div>
      <div className="sectionSmallLabel">
        {ReasonReact.string("Add player")}
      </div>
      <div className="section"> <AddPlayer /> </div>
      <hr />
      <div className="sectionSmallLabel">
        {ReasonReact.string("Remove last game")}
      </div>
      <div className="section"> <RemoveGame /> </div>
    </div>,
};