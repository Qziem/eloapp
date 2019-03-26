[@bs.deriving jsConverter]
type luminosity = [ | `bright | `light | `dark];

[@bs.deriving jsConverter]
type format = [ | `rgb | `rgba | `rgbArray | `hsl | `hsla | `hslArray | `hex];

[@bs.deriving jsConverter]
type hue = [
  | `red
  | `orange
  | `yellow
  | `green
  | `blue
  | `purple
  | `pink
  | `monochrome
];

[@bs.deriving abstract]
type randomColorOptions = {
  [@bs.optional]
  seed: option(string),
  [@bs.optional]
  count: option(int),
  [@bs.optional]
  alpha: option(float),
  hue: option(string),
  format: string,
  luminosity: string,
};

[@bs.module]
external randomColorExternal: randomColorOptions => string = "randomcolor";

let randomColor =
    (
      ~luminosity=`bright,
      ~format=`hex,
      ~hue=?,
      ~hueHex=?,
      ~seed=?,
      ~count=?,
      ~alpha=?,
      (),
    ) => {
  let hueVal =
    switch (hueHex, hue) {
    | (Some(value), _) => Some(value)
    | (None, Some(value)) => Some(hueToJs(value))
    | (None, None) => None
    };

  let isAlphaValid = (alpha: option(float)) => {
    switch (alpha) {
    | None => true
    | Some(value) => value >= 0.0 && value <= 1.0
    };
  };

  if (isAlphaValid(alpha) === false) {
    Js.Console.error(
      "Alpha value is not valid (should be float between 0 and 1).",
    );
  };

  let colorOpts =
    randomColorOptions(
      ~seed,
      ~count,
      ~alpha,
      ~luminosity=luminosityToJs(luminosity),
      ~format=formatToJs(format),
      ~hue=hueVal,
      (),
    );

  randomColorExternal(colorOpts);
};
