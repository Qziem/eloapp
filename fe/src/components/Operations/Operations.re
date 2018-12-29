/* [%bs.raw {|require('./Users.scss')|}]; */
let component = ReasonReact.statelessComponent("Operations");

let make = _children => {
  ...component,
  render: _self =>
    <div>
      <h5> {ReasonReact.string("Add player")} </h5>
      <div className="section"> <AddPlayer /> </div>
      <hr />
      <h5> {ReasonReact.string("Remove last game")} </h5>
      <div className="section"> <RemoveGame /> </div>
    </div>,
};