export const H3 = (props: {
  children: React.ReactNode
  className?: string
}) => {
  const defaultClasses = 'tw-text-20 sm:tw-text-24 md:tw-text-32 lg:tw-text-48'
  const classes = props.className
    ? `${defaultClasses} ${props.className}`
    : defaultClasses
  return <h3 className={classes}>{props.children}</h3>
}
