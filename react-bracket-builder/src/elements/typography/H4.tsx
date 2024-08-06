export const H4 = (props: {
  children: React.ReactNode
  className?: string
}) => {
  const defaultClasses = 'tw-text-16 sm:tw-text-20 md:tw-text-24 lg:tw-text-32'
  const classes = props.className
    ? `${defaultClasses} ${props.className}`
    : defaultClasses
  return <h4 className={classes}>{props.children}</h4>
}
