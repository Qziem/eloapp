[%bs.raw {|require('./LoadingMask.scss')|}];
let component = ReasonReact.statelessComponent("LoadingMask");

let make = _children => {
  ...component,
  render: _self =>
    <div className="loadingMask">
      <i className="fas fa-spinner fa-spin" />
    </div>,
};